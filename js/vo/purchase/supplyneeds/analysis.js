/**
 * @projectDescription This file will abstract products and ask the server via ajax for any data in needs in it's
 *                     functioning. I will attempt to follow scriptdoc standard for JS code. It's primary purpose is to
 *                     display sales data and predicted sales data for a magento installation in graphs and tables; and
 *                     to allow the export of the displayed data in .csv and image form.
 * 
 * As well as this file this use case includes/requires a php controller for ajax interfaces to other php classes, a
 * template and block files in standard magento form, and several JS libraries and plugins including jQuery, table2CSV,
 * and flot.
 * 
 * A special note, the version of flot required has been modified by myself with special functionality in the legend
 * writing code. Without that modification the legend color will not be populated into the sku list. Just in case you
 * acciently update flot and lose this code here it is, as of V. 1.1 this is inserted between lines 2181 and 2182, right
 * after s is defined is fine:
 * 
 * if('sku' in s) { //Special velo-orange addition /* If this series has sku it should be placed in special sku legend.
 * The sku legend is a complicated list with tree view, but the area we care about alawys has the id = sku and class =
 * "sku" Further we never replace the thing, we add the color, any triggers reffering to this series, and open the eye.
 * [backslash] jQuery('#'+s.sku+' .color:first').html('<div style="width:90%; height:90%; border:1px solid white;
 * background: ' + s.color + ';"></div>'); jQuery('#'+s.sku+' .eye:first').replaceWith('<img
 * onclick="productManifest['+s.productId+'].toggleVisibility()" src="'+window.info.eye_open_url+'" class="eye">'); }
 * 
 * @author Trygve Skogsholm trygve@velo-orange.com
 * @version 1.0
 */

// ////////////////////
// Global variables: //
// ////////////////////
/**
 * An object which describes various values loaded with PHP in the template file (graph.phtml)
 * 
 * @alias window.info
 * @property {Object}
 */
var info = window.info;

/*
 * Product manifest is an object-list of product ids already abstracted. This is to prevent them from being loaded
 * twice. To check for existence use the 'in' operator. The value is merely a reference to the object.
 */
var productManifest = {};

/*
 * plot object is flot graph.
 */
var plot;
var chartOptions =
{
    xaxis :
    {
	    mode : 'time'
    },
    grid :
    {
        autoHighlight : true,
        hoverable : true
    }
};

// this variable acts as a placeholder for the modified series so that the data does not have to be modified when
// aggregated.
var currentSeries;
var graphAggregation = 'month';
var showTable = true;

/**
 * @classDescription An abstraction of a magento product, specifically the sales data.
 * @constructor
 * @param {Int} [productId] Magento product ID (also called entity_id).
 * @param {Object} [data] A list of properties to copy to the object.
 * @return {Product} [this] Returns the constructed object, does not contain series data
 */
function Product(productId, data)
{
	/*
	 * To initialize the product properly we need to detect whether it is a parent or a child to do this we are going to
	 * have to use an AJAX function and place the following logic in the call back.
	 * 
	 * This object may contain many properties but some are expected at all times: sku, isParent
	 * 
	 * It also contains a property [data] in which it stores it's various data as SERIES objects which themselves
	 * contain flot series.
	 * 
	 * In the case of parents pretty much every function merely aggregates it's children, so make sure to access all
	 * this stuff through provided functions who know how to deal with the differences when it exists.
	 */
	// Before anything else realize that the sku should be totally unique in the manifest
	if (productId in productManifest)
	{
		return false;
	}
	// Otherwise the item must be removed from the sku search list
	for ( var int = 0; int < window.info.autocomplete.length; int++)
	{
		if (window.info.autocomplete[int].value == productId)
		{
			window.info.autocomplete.splice(int, 2);
			break;
		}
	}
	jQuery('#newSku').autocomplete("option", "source", window.info.autocomplete);

	/**
	 * Magento product id, since it is unique in sql it is prime candidate for unique identifier here.
	 * 
	 * @alias Product.id
	 * @property {Int}
	 */
	this.id = productId;

	this.data =
	{
	    predicted : {},
	    actual : {}
	}; // Object containing aggregation properties plus a running total dataset
	this.isParent = false; // Refers to magento's configurable product system in which some products are 'associated'
	// with children.

	this.showChildren = false;

	// Add this object to a global list, based on it's id to be easily accessed later; this is by reference only so you
	// don't need special logic to deal with accessing the 'one in the list' (thank god)
	productManifest[this.id] = this;

	// Copy properties, these usually come from the ajax call.
	if (data)
	{
		for (property in data)
		{
			this[property] = data[property];
		}
	}

	// this function will be called when the product is initialized with the data from AJAX
	this.readyAction = function()
	{
		// Remove sku from search list, it's by reference

		// Draw ui
		if (this.type != 'child')
		{
			this.drawUI();
		}
		// Show basic data
		this.graphData('actual');
		return;
	};

	/*
	 * The following asks the server for the extra data it needs on this product, but it is possible that it was already
	 * populated with the data variable above, sku and name are proxies for this possibility and this function will not
	 * be called if they exist.
	 */
	if (('name' in this && 'sku' in this) == false)
	{
		// set this to true when we have received the initialization data, any functions relying on variables other than
		// product_id should check this first
		this.ready = false;

		// setup a temporary variable because we won't be able to use this in the ajax.
		var product = this;
		jQuery.ajax(
		{
		    url : window.info.getSkuDataUrl, // PHP generated magento controller URL to supplyController.php ->
		    // getSkuDataUrlAction() in graph.phtml
		    dataType : "json",
		    async : true,
		    data :
		    {
			    id : product.id
		    },
		    success : function(response)
		    {
			    // Copy data, the idea here is exactly the same as the above for data variable
			    for (property in response.data)
			    {
				    product[property] = response.data[property];
			    }

			    // If the id we sent is for a parent product there may be some children to consider, we should also have
			    // received data for them which will save us further calls
			    if ('children' in response)
			    {
				    product.children = []; // Create children array in parent
				    product.isParent = true; // Inform it that it is a parent
				    for (childId in response.children)
				    {
					    var child = response.children[childId];
					    child.parent = product;
					    // var child in this context is just some data, including name, sku, and type; this will cause
					    // the child to not make this ajax call itself upon construction
					    product.children.push(new Product(childId, child));
				    }
			    }
			    product.ready = true; // Inform product that is ready
			    product.readyAction(); // Perform any functions triggered by being ready
		    }
		});
	} else
	{
		// If the data is there already just declare things ready
		this.ready = true; // Inform product that is ready
		this.readyAction(); // Perform any functions triggered by being ready
	}

	// There are also display properties to be considered:
	this.visible = true;
};

/**
 * Retrieves data from a local property of this object, if it doesn't exist it will retrieve it from the server. Thus
 * this is a safe gateway to ask for data and not to worry about what's been loaded yet.
 * 
 * @memberOf Product
 * @method
 * @param {String}[type] standard request.
 * @param {Object} [options] various properties that may be required for certain requests.
 * @return {Object,undefined} flot series object as a reference from local property.
 * @example this function will give you the referenced Series property, Product.getData('predicted-linear-month');
 */
Product.prototype.getData = function(type, options)
{
	/**
	 * There is a special interface to access various data, a string separated by '-' declaring the properties (and thus
	 * location) of a Series object. For instance "predicted-linear-month" or "actual" collectively called 'type' or
	 * 'request' refers to this.data.predicted.linear.month instanceof Series. There are five parts to this function
	 * 
	 * @Part:I - initialize and prepare variables.
	 * @Part:II - Parse request into local variables (such as isPredicted) which completly describe the request.
	 * @Part:III - Deal with the possibility of this being a parent, which is essentialy calling this on the simple
	 *           children. This will return the function before parts IV and V because that logic is done in the
	 *           children.
	 * @Part:IV - Check to see if the data already exists, return it if it does.
	 * @Part:V - If it doesn't retrieve it via ajax from the server.
	 */
	// ////////////////////////////////////////////
	// Part I, initialize and prepare variables. //
	// ////////////////////////////////////////////
	var id = this.id;
	var product = this;
	var data;

	// ///////////////////////////////////////////////
	// Part II, Parse request into local variables. //
	// ///////////////////////////////////////////////
	var typeArray = type.split('-', 3);

	// First item should always be the predicted status
	var isPredicted = typeArray.shift();
	if (isPredicted == 'predicted')
	{
		isPredicted = true;
		// We'll need two more, the model and target aggregation.
		var predictionModel = typeArray.shift();
		var targetAggregation = typeArray.shift();
	} else if (isPredicted == 'actual')
	{
		isPredicted = false;
	}

	// //////////////////////////////////////////////////////////////
	// Part III, Deal with the possibility of this being a parent. //
	// //////////////////////////////////////////////////////////////
	if (this.isParent)
	{
		var seriesOptions =
		{
		    sku : this.sku,
		    productId : this.id
		};
		if (isPredicted)
		{
			if (predictionModel in this.data.predicted && targetAggregation in this.data.predicted[predictionModel])
			{
				return this.data.predicted[predictionModel][targetAggregation];
			} else
			{
				seriesOptions.label = this.sku + " Sum Prediction";
				var compositeData = new Array();
				for ( var i = 0; i < this.children.length; i++)
				{
					var childSeries = (this.children[i]).getData(type, options);
					compositeData = compositeData.concat(childSeries.data);
					// defaults to hiding children
					this.children[i].visible = this.showChildren;
				}
				var data = new Series(
				{
					data : compositeData
				}, seriesOptions);
				if (!(predictionModel in this.data.predicted))
				{
					this.data.predicted[predictionModel] = {};
				}
				this.data.predicted[predictionModel][targetAggregation] = data;
				return this.data.predicted[predictionModel][targetAggregation];
			}
		} else
		{
			var furtherType = typeArray.shift();
			if (furtherType == 'sum' || furtherType == undefined)
			{
				if ("sum" in this.data.actual)
				{
					return this.data.actual.sum;
				} else
				{
					this.data.actual.sum = new Object();
				}
				seriesOptions.label = this.sku + " Sum Sales";
				var compositeData = new Array();
				for ( var i = 0; i < this.children.length; i++)
				{
					var childSeries = (this.children[i]).getData(type, options);
					compositeData = compositeData.concat(childSeries.data);
					// defaults to hiding children
					this.children[i].visible = this.showChildren;
				}
				var data = new Series(
				{
					data : compositeData
				}, seriesOptions);
				this.data.actual.sum = data;
				return this.data.actual.sum;
			} else if (furtherType == 'median')
			{
				if ("median" in this.data.actual)
				{
					return this.data.actual.median;
				} else
				{
					this.data.actual.median = new Object();
				}
				seriesOptions.label = this.sku + " Median Sales";
				var compositeData = new Array();
				var length = this.children.length;
				for ( var i = 0; i < this.children.length; i++)
				{
					var childSeries = (this.children[i]).getData(type, options);
					compositeData = compositeData.concat(childSeries.data);
					compositeData = compositeData.map(function(point)
					{
						point[1] = point[1] / length;
						return point;
					});
					// defaults to hiding children
					childSeries.visible = false;
				}
				var data = new Series(
				{
					data : compositeData
				}, seriesOptions);
				this.data.actual.median = data;
				return this.data.actual.median;
			}
		}
	}

	// ////////////////////////////////////////////////////
	// Part IV, Check to see if the data already exists. //
	// ////////////////////////////////////////////////////
	/*
	 * Alright we have the data we need from the type to know exactly what data we need. The next step is to see if it
	 * exists already or if we have to get it ourselves. To do this we will engage in a series of 'in' operator checks.
	 * The reason for doing it as a series instead of all at once is to fill in the blanks along the way.
	 * 
	 * All we know for sure is that predicted and actual will be there.
	 */
	if (isPredicted)
	{
		if (predictionModel in this.data.predicted && targetAggregation in this.data.predicted[predictionModel])
		{
			return this.data.predicted[predictionModel][targetAggregation];
		}
	} else
	{
		if (this.data.actual instanceof Series)
		{
			return this.data.actual;
		}
	}

	// ////////////////////////////////////////////////////////////////////
	// Part V, If it doesn't exist retrieve it via ajax from the server. //
	// ////////////////////////////////////////////////////////////////////
	/*
	 * If you've got to this point the data doesn't exist already, we'll need to get it from the server This can get
	 * quite messy with all the ajax functions floating around, most will be taken care of by the two functions for
	 * aggregate data and predicted data and subsequent variables but you may still get a headache if you aren't used to
	 * jquery with ajax. The final goal of the onSuccess functions will be to set the products data and return it.
	 */
	if (isPredicted)
	{
		// We need to know how long we are trying to predict, take the
		// get predicted data from server based on model, target, and projection time.
		if (this.type != 'child')
		{
			var to = (this.controlPanel.find('.projection').datepicker("getDate")).getTime();
		} else
		{
			var to = (this.parent.controlPanel.find('.projection').datepicker("getDate")).getTime();
		}

		jQuery.ajax(
		{
		    url : window.info.getPredictionUrl,
		    dataType : "json",
		    async : false,
		    data :
		    {
		        id : product.id,
		        from : product.data.actual.data[0][0],
		        to : to,
		        model : predictionModel,
		        target : targetAggregation
		    },
		    success : function(response)
		    {
			    options =
			    {
			        label : product.sku + " Prediction",
			        sku : product.sku,
			        productId : product.id
			    };
			    if (!(predictionModel in product.data.predicted))
			    {
				    product.data.predicted[predictionModel] = {};
			    }
			    product.data.predicted[predictionModel][targetAggregation] = new Series(response, options);
		    }
		});
		return this.data.predicted[predictionModel][targetAggregation];
	} else
	{
		jQuery.ajax(
		{
		    url : window.info.getPlainSeriesUrl,
		    dataType : "json",
		    async : false,
		    data :
		    {
			    id : product.id
		    },
		    success : function(response)
		    {
			    options =
			    {
			        label : product.sku + " Sales",
			        sku : product.sku,
			        productId : product.id
			    };
			    product.data.actual = new Series(response, options);
		    }
		});
		return this.data.actual;
	}
};

Product.prototype.generatePrediction = function()
{
	var model = this.controlPanel.find('#model-select').val();
	var target = this.controlPanel.find('#target-select').val();
	this.graphData('predicted-' + model + '-' + target);
};

/**
 * Graph/display a member data Series for this product
 * 
 * @memberOf Product
 * @method
 * @param {String} [request] String describing what set of data you want to graph.
 * @param {Boolean} [noRefresh] When calling this on several requests, set this to true to avoid unneccesary
 *            recompilations. You will need to call graphData (not this.graphData) yourself at the end of your list in
 *            this case.
 * @return {undefined}
 */
Product.prototype.graphData = function(request, noRefresh)
{
	/*
	 * The request is identical to the type argument made to getData(type, options), you will call this function as a
	 * gate way for "show me the data I don't care how". It will call get data which assure that it exists. This further
	 * assures it will will be visible. After that, all it needs to do is call graph data which will recompile all
	 * datasets which you are now sure contains the one you requested.
	 */
	if (this.ready)
	{
		var data = this.getData(request);
		data.visible = true;
		if (noRefresh != true)
		{
			graphData();
		}
	}
};

/**
 * Toggle child visibility
 * 
 * @memberOf Product
 * @method
 * @param {Boolean} show
 * @return {undefined}
 */
Product.prototype.childVisibility = function(show)
{
	this.showChildren = show;
	for ( var i = 0; i < this.children.length; i++)
	{
		var child = this.children[i];
		child.visible = this.showChildren;
		if (this.showChildren == false)
		{
			child.hideProduct();
		}
	}
	graphData();
	return;
};

/**
 * Effects the change to this product (this.visible = false) and the ui changes required to hide a product, and
 * refreshes the graph.
 * 
 * @memberOf Product
 * @method
 * @return {undefined}
 */
Product.prototype.hideProduct = function()
{
	var id = this.id;
	this.visible = false;
	jQuery('#' + this.sku + ' .color:first').html('');
	jQuery('#' + this.sku + ' .eye:first').replaceWith(
	        '<img onclick="productManifest[' + id + '].toggleVisibility()" src="' + window.info.eye_closed_url
	                + '" class="eye">');
	graphData();
};

/**
 * The opposite of hideProduct()
 * 
 * @memberOf Product
 * @method
 * @return {undefined}
 */
Product.prototype.showProduct = function()
{
	var id = this.id;
	this.visible = true;
	jQuery('#' + this.sku + ' .eye:first').replaceWith(
	        '<img onclick="productManifest[' + id + '].toggleVisibility()" src="' + window.info.eye_open_url
	                + '" class="eye">');
	graphData();
};

/**
 * Toggles visibility of an entire product or any of it's series based on request, undefined request indicates whole
 * product. If using checkbox, value of checkbox must be the request if using function otherwise.
 * 
 * @memberOf Product
 * @method
 * @param {String, jQuery checkbox element, undefined} [request] what to toggle visibility on. String is the same as
 *            getData etc..
 * @return {Boolean} current visibility.
 */
Product.prototype.toggleVisibility = function(request)
{
	if (request == undefined)
	{
		if (this.visible)
		{
			this.hideProduct();
		} else
		{
			this.showProduct();
		}
		return this.visible;
	}
	var hardCodedValue = false;
	if (typeof request != 'string')
	{
		hardCodedValue = true;
		try
		{
			if (request.is(':checked'))
			{
				var override = true;
			} else
			{
				var override = false;
			}
			request = request.val();
		} catch (e)
		{
			throw "Request for toggleVisibility must be either null, a string or input element with value = string. "
			        + e;
		}
	}
	var data = this.getData(request);
	if (hardCodedValue)
	{
		data.visible = override;
	} else
	{
		data.visible = !data.visible;
	}
	if (data.visible)
	{
		this.graphData(request);
	} else
	{
		this.hideData(request);
	}
	return data.visible;
};

/**
 * Basically the opposite of this.graphData(), this assures that a specific Series is not displayed as opposed to the
 * whole product like the immediately preceding functions. Although such a request does not require data it still calls
 * getData, luckily it shouldn't come up that you need to hide data you haven't displayed, but if you do this accidently
 * the worse that will happen is some wasted runtime. It also effects the visual changes on the whole product which is
 * something that may need to be dealt with more properly in the future. The opposite is done by a modification to
 * flot's legend writing code.
 * 
 * @memberOf Product
 * @method
 * @param {String}[request] standard request used by getData and graphData.
 * @param {Boolean} [noRefresh] When calling this on several requests, set this to true to avoid unneccesary
 *            recompilations. You will need to call graphData yourself at the end of your list in this case.
 * @return {undefined}
 */
Product.prototype.hideData = function(request, noRefresh)
{
	if (this.ready)
	{
		var id = this.id;
		var data = this.getData(request);
		data.visible = false;
		jQuery('#' + data.sku + ' .color:first').html('');
		jQuery('#' + data.sku + ' .eye:first').replaceWith(
		        '<img onclick="productManifest[' + id + '].toggleVisibility()" src="' + window.info.eye_closed_url
		                + '" class="eye">');
		if (noRefresh != true)
		{
			graphData();
		}
	}
};

/**
 * This function will cause the control panel to be shown again after it was hidden.
 * 
 * @memberOf Product
 * @method
 * @return {undefined}
 */
Product.prototype.focus = function()
{
	jQuery('.control-panel').hide();
	this.controlPanel.show();
};

/**
 * looks for any children in this Product object under a property of the same name
 * 
 * @memberOf Product
 * @method
 * @return {Boolean}
 */
Product.prototype.hasChildren = function()
{
	var hasChildren = false;
	if ('children' in this && this.children.length > 0)
	{
		hasChildren = true;
	}
	return hasChildren;
};

/**
 * This function handles the creation of ui elements for new products, it is expected to only be called on new Products
 * either in the constructor or in the readyAction function.
 * 
 * @memberOf Product
 * @method
 */
Product.prototype.drawUI = function()
{
	if (this.ready)
	{
		/*
		 * There are two primary elements to the UI in this tool, the product 'control panel' and the entry in a list of
		 * skus which reflect the product manifest.
		 * 
		 * This list will allow you to 'select' a sku and thus show the control panel, it will show children below
		 * parents in such a manner as to make their relation obvious (a tree or something)
		 * 
		 * The control panel will be a jquery object which is stored in this product. It will contain tools UI for
		 * exporting data, limiting date ranges when graphing, changing aggregation, showing, hiding, inputig parameters
		 * for prediction modeling etc...
		 */

		// List
		var listItem = jQuery('<li id="' + this.sku + '" onClick="productManifest[' + this.id + '].focus()">');
		listItem.addClass('sku');
		var anchor = jQuery('<a>');
		anchor.append('<img class="eye" onclick="productManifest[' + this.id + '].toggleVisibility()" src="'
		        + window.info.eye_closed_url + '" />');
		anchor.append('<div class="color"></div>');
		anchor.append('<div class="label">' + this.sku + '</div>');
		listItem.append(anchor);

		if (this.hasChildren())
		{
			var childrenList = jQuery('<ul>');
			childrenList.addClass('children');
			for ( var i = 0; i < this.children.length; i++)
			{
				childrenList
				        .append('<li class="sku child" id="' + this.children[i].sku
				                + '"><a><img class="eye" onclick="productManifest[' + this.children[i].id
				                + '].toggleVisibility()" src="' + window.info.eye_closed_url
				                + '" /><div class="color"></div><div class="label">' + this.children[i].sku
				                + '</div></a></li>');
			}
			listItem.append(childrenList);
		}

		this.listItem = listItem;
		listItem.appendTo(jQuery('#list'));
		/*
		 * jQuery('#list').jstree( { "plugins" : [ "themes", "html_data" ] });
		 */

		// Control
		var controlElement = jQuery('#control-template').clone();
		controlElement.prop('id', this.id + '-control');
		controlElement.find('.control-title').append(this.sku + ' ~ <small>' + this.name + '</small>');
		controlElement.find('.projection').datepicker();
		var product = this;

		var content = controlElement.find(".ui-widget-content");
		if (this.hasChildren())
		{
			content.append('<button onClick="productManifest[' + product.id
			        + '].childVisibility(true);" >Show Children</button>');
			content.append('<button onClick="productManifest[' + product.id
			        + '].childVisibility(false);" >Hide Children</button>');
		}

		// Get prediction, two parmeters: model and target.
		content.append('<button onClick="productManifest[' + product.id
		        + '].generatePrediction();" >Generate Prediction</button>');
		/*
		 * // Data controls and checkboxes var checkboxContainer = controlElement.find('.checkboxes');
		 * addCheckboxOption(this.id, 'actual-none', 'Exact', checkboxContainer); addCheckboxOption(this.id,
		 * 'actual-day', 'Day', checkboxContainer); addCheckboxOption(this.id, 'actual-week', 'Week',
		 * checkboxContainer); addCheckboxOption(this.id, 'actual-2week', 'Two Week', checkboxContainer);
		 * addCheckboxOption(this.id, 'actual-month', 'Month', checkboxContainer); addCheckboxOption(this.id,
		 * 'actual-quarter', 'Quarter', checkboxContainer); addCheckboxOption(this.id, 'actual-year', 'Year',
		 * checkboxContainer); addCheckboxOption(this.id, 'actual-lifetime', 'Lifetime', checkboxContainer);
		 * addCheckboxOption(this.id, 'actual-runningTotal', 'Running Total', checkboxContainer);
		 */
		// addCheckboxOption('productManifest[' + this.id + '].childVisibility(jQuery(this).is(\':checked\'));', null,
		// "Show Children", controlElement);
		var dataOptions = controlElement.find('.data-option');
		if (this.isParent)
		{
			dataOptions.hover(function()
			{
				jQuery(this).addClass("dataTypeHighlight");
			}, function()
			{
				jQuery(this).removeClass("dataTypeHighlight");
			});
		}

		if (this.type != 'child')
		{

		}
		this.controlPanel = controlElement;
		jQuery('#control-container').append(this.controlPanel);
		this.focus();
	}
};

/*
 * Flot graphing functions:
 */

/**
 * Despite having the same name as the Product member function graphData(request,noRefresh), this function is not a
 * global version of that nor does it reference and specific Series. Instead it is more like a 'refresh graph' function,
 * it gathers all Series from all Product instances discriminating based on their visibility properties and compiles
 * them into one giant list of data to display. It then displays the data in graph and table form. This is done
 * everytime the way the graph is displayed or the underlying data is changed. This may seem unnessary but in my opinion
 * it's a lot better than the bugs that would result from trying to deal with all the dependancies, on my computer it
 * doesn't take long; and there are no server calls involved in this function itself.
 * 
 * This function also does some manipulation of the data before handing it off the flot and drawTable, it will
 * aggregate, and constain time, and some other options based on html user input.
 * 
 * @return {Object} jQuery.plot the new plot
 */
function graphData()
{
	/*
	 * The two essential parts of this function are get the data aggregate it and then setup the map keys
	 */
	// Gather all visible series
	var seriesArray = new Array();
	var productSeries;
	var product;
	for ( var id in productManifest)
	{
		product = productManifest[id];
		if (product.visible)
		{
			productSeries = findAllSeries(product.data);
			for ( var i = 0; i < productSeries.length; i++)
			{
				var series = productSeries[i];
				if (series.visible)
				{
					seriesArray.push(series);
				}
			}
		}
	}
	// Determine how the chart will look
	chartOptions.series = {};
	var displayType = jQuery('#display-select').val();
	if (displayType == 'lines')
	{
		chartOptions.series.lines =
		{
			show : true
		};
	} else if (displayType == 'bars')
	{
		chartOptions.series.bars =
		{
		    show : true,
		    align : "center",
		    barWidth : 0.9
		};
	} else
	{
		chartOptions.series.bars =
		{
			show : false
		};
		chartOptions.series.lines =
		{
			show : false
		};
	}

	chartOptions.series.points =
	{
		show : jQuery('#show-points').is(':checked')
	};

	// Get override options
	if (graphAggregation == 'none')
	{
		// never connect the dots for exact
		chartOptions.series =
		{
		    points :
		    {
			    show : true
		    },
		    lines :
		    {
			    show : false
		    },
		    bars :
		    {
			    show : false
		    }
		};
	} else if (graphAggregation == 'lifetime')
	{
		// always show just bar for lifetime
		chartOptions.series =
		{
		    points :
		    {
			    show : false
		    },
		    lines :
		    {
			    show : false
		    },
		    bars :
		    {
			    show : true
		    }
		};
	}

	// Need to do this so that we don't pass by reference and destroy our original data during aggregation
	currentSeries = clone(seriesArray);
	limitSeriesTime(currentSeries, getFromDate(), getToDate());
	aggregateSeries(currentSeries, graphAggregation, false, chartOptions);

	// Update Table
	if (showTable)
	{
		drawTable(currentSeries, jQuery('#table-container'));
	}
	return plot = jQuery.plot(jQuery('#chart-area'), currentSeries, chartOptions);
}

/**
 * This function draws the table, using the exact same data as the graph and is called by graphData (which does some
 * manipulation)
 * 
 * @param {Array} [visibleSeries] Array of Series objects
 * @param {jQuery element} [container] the inner contents are replaced with the table
 * @return {jQuery element} the table.
 */
function drawTable(visibleSeries, container)
{
	var table = jQuery('<table>');
	var tableBody = jQuery('<tbody>');
	var tableHeader = jQuery('<thead>');
	tableHeader.append(jQuery('<tr>'));

	var rows = [];

	if (graphAggregation == 'none')
	{
		// Difference here is going to be that we need to parse the date value
		tableHeader.find('tr:first').append('<th>Date</th><th>SKU</th><th>Value</th>');
		for ( var seriesIndex = 0; seriesIndex < visibleSeries.length; seriesIndex++)
		{
			var series = visibleSeries[seriesIndex];
			for ( var pointIndex = 0; pointIndex < series.data.length; pointIndex++)
			{
				var date = new Date(series.data[pointIndex][0]);
				tableBody.append('<tr><td>' + date + '</td><td>' + series.sku + '</td><td>'
				        + series.data[pointIndex][1] + '</td></tr>');
			}
		}
	} else if (graphAggregation == 'lifetime')
	{
		// two columns sku & values
		tableHeader.find('tr:first').append('<th>SKU</th><th>Sales</th>');
		for ( var seriesIndex = 0; seriesIndex < visibleSeries.length; seriesIndex++)
		{
			var series = visibleSeries[seriesIndex];
			tableBody.append('<tr><td>' + series.sku + '</td><td>' + series.data[0][1] + '</td></tr>');
		}
	} else
	{
		// Standard, columns should be sales (or predicted sales), first column is always period
		// add first header
		tableHeader.find('tr:first').append('<th>Period</th>');

		for ( var seriesIndex = 0; seriesIndex < visibleSeries.length; seriesIndex++)
		{
			var series = visibleSeries[seriesIndex];

			// add header
			tableHeader.find('tr:first').append('<th>' + series.label + '</th>');
			for ( var pointIndex = 0; pointIndex < series.data.length; pointIndex++)
			{
				// add row, remember pointIndex is identical to the integer x value
				if (rows[series.data[pointIndex][0]] == undefined)
				{
					rows[series.data[pointIndex][0]] = [];
				}
				rows[series.data[pointIndex][0]][seriesIndex] = series.data[pointIndex][1];
			}
		}

		// go through the rows and actually add the data
		for ( var x = 0; x < rows.length; x++)
		{
			// setup row
			var row = jQuery('<tr>');

			// Insert period key
			row.append('<td>' + chartOptions.xaxis.ticks[x][1] + '</td>');

			for ( var columnIndex = 0; columnIndex < visibleSeries.length; columnIndex++)
			{
				var value = 0;
				if (rows[x][columnIndex] != undefined)
				{
					value = rows[x][columnIndex];
				}
				// add the value, if it's undefined it should just be a blank string
				row.append('<td>' + value + '</td>');
			}
			tableBody.append(row);
		}
	}

	table.append(tableHeader);
	table.append(tableBody);
	container.html(table);
	return table;
}

/**
 * Takes an array of series and removes all points outside of a date range.
 * 
 * @param {Array} [series] Array of Series objects to manipulate
 * @param {Date,Int,null} [from] From date, if null no limitation will occur
 * @param {Date,Int,null} [to] To date, if null no limitation will occur
 * @return {Series} returns modified array of series, since it was by reference this is courtesy.
 */
function limitSeriesTime(series, from, to)
{
	// If both null no point in doing this
	if (from == null && to == null)
	{
		return series;
	}

	// if not already in int form convert
	if (from && from instanceof Date)
	{
		from = from.getTime();
	}
	if (to && to instanceof Date)
	{
		to = to.getTime();
	}

	// This function merely takes the series array and removes any points beyond the scope of from and to
	for ( var seriesIndex = 0; seriesIndex < series.length; seriesIndex++)
	{
		for ( var i = 0; i < series[seriesIndex].data.length; i++)
		{
			var point = series[seriesIndex].data[i];
			if (from && point[0] < from)
			{
				// remove point, it's from before the from time
				series[seriesIndex].data.splice(i, 1);
				// This alters the index of the array by removing one, we are still at the right place for the next
				// tested value so counteract the for loop.
				i--;
			}
			if (to && point[0] > to)
			{
				// remove point, it's from before the from time
				series[seriesIndex].data.splice(i, 1);
				i--;
			}
		}
	}
	return series;
}

/**
 * This function will modify an array of series by combining datapoints from the same period together, ussualy by
 * summing them into one point.
 * 
 * @param {Array} [series] Array of Series objects to manipulate.
 * @param {String} [aggregation] String describing the period of aggregation desired.
 * @param {Boolean} [fillGaps] Based on the aggregation make sure every period is represented.
 * @param {Object} [options] chartOptions to modify with new tick index.
 * @return {Array} [series] Modified version of series, since series was passed by reference this is mere courtesy.
 */
function aggregateSeries(series, aggregation, fillGaps, options)
{
	/*
	 * Takes an array of series, groups each by period; and sums them all together (aggregates). If there is only one
	 * series the function will only aggregate.
	 * 
	 * The key use of this is to generate the map of x values to text description of the periods. Thus to properly
	 * associate data each series will be assigned to a periods array. The periods array contains a period object which
	 * contains a timestamp from within the period (or at the very start) as well as a value array where each index
	 * leads to a summed y value. Remember since we are aggregating for any given period there will only be one point
	 * per series representing all the y values sum (and possibly other functions) An examlple object would look like
	 * this:
	 * 
	 * periods: [ { period: "June 2015", timestamp: 1435622400000, values: [42,14,16] <- index is identical to original
	 * series index, values are aggregate y values }, { ... } ]
	 * 
	 * We will sort the list based on the timestamp property to make sure it is in the correct order. Then we will go
	 * through each period using the index as the x value. Since we sorted by time we can be sure that the lower index
	 * values will be earlier in time. The index/x value will be used to create a new point in a new set of series. i.e.
	 * for each period for each value a new point will be created in a series whose index is identical to the value
	 * index, whose x value is the period index and whose y value is the value in the values array. Further for each
	 * period a map will be generated relating the index/x values with the period string (this is ticks property). This
	 * will be passed to format the final chart. Obviously the x-values may change if we add new periods and thus this
	 * will have to be called every time we draw the chart to be sure everything is correct, further we should not alter
	 * the local data.
	 * 
	 * It may be that we need to fill in periods when there is no data between them. To do this we generate a list of
	 * all periods between the highest and lowest period timestamps before and initialize to zero y value for each
	 * series.
	 */
	if (aggregation == false || aggregation == 'none' || series.length == 0)
	{
		options.xaxis.ticks = null;
		options.xaxis.mode = 'time';
		return;
	}

	var newSeries = {};
	var map = new Array();
	var periods = new Array();
	var periodMap = {};
	for ( var seriesIndex = 0; seriesIndex < series.length; seriesIndex++)
	{
		for ( var i = 0; i < series[seriesIndex].data.length; i++)
		{
			var point = series[seriesIndex].data[i];
			var period = getPeriod(point[0], aggregation);

			if (period in periodMap == false)
			{
				// This period and timestamp are representative.
				periodMap[period] =
				{
				    values : {},
				    period : period,
				    timestamp : point[0]
				};
			}

			if (seriesIndex in periodMap[period].values)
			{
				periodMap[period].values[seriesIndex] += point[1];
			} else
			{
				periodMap[period].values[seriesIndex] = point[1];
			}
		}
	}

	// form into array for sorting
	for (period in periodMap)
	{
		periods.push(periodMap[period]);
	}

	// sort
	periods.sort(function(a, b)
	{
		return a.timestamp - b.timestamp;
	});

	// generate map and new series
	for ( var x = 0; x < periods.length; x++)
	{
		map.push([ x, periods[x].period ]);
		for (seriesIndex in periods[x].values)
		{
			if (seriesIndex in newSeries == false)
			{
				newSeries[seriesIndex] = new Array();
			}
			newSeries[seriesIndex].push([ x, parseInt(periods[x].values[seriesIndex]) ]);
		}
	}

	for ( var seriesIndex in newSeries)
	{
		var data = newSeries[seriesIndex];
		seriesIndex = parseInt(seriesIndex);
		series[seriesIndex].data = data;
	}
	options.xaxis.ticks = map;
	options.xaxis.mode = null;
	return series;
}

/**
 * Given a timestamp and aggregation type this function returns a string of the period to which that date belongs
 * 
 * @param {Date|Int} [date] exact date at which data point occured
 * @param {String} [aggregation] the desired aggregation period
 * @return {String} [Period] a string describing the actual period the date falls within.
 */
function getPeriod(date, aggregation)
{
	if (date instanceof Date == false && isNaN(date) == false)
	{
		date = new Date(date);
	}
	// Highest common scope is year (doesn't repeat)
	var period;

	// To add a possible aggregation grouping add a case statement and follow the pattern
	switch (aggregation)
	{
	case 'none':
		return;
		break;
	case 'day':
		return (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();
		break;
	case 'week':
		/*
		 * No built in function for week or two week, but we can get it using the following logic: consider the interval
		 * in ms between the given date and the exact start of that year which is date.getTime - [new
		 * Date(date.getFullYear(), 0, 0, 0, 0, 0, 0)].getTime(). If we take this interval and divide by the number of
		 * ms in a week (or two weeks) we will come to a decimal number, the integer portion of which is the whole weeks
		 * since the start of the year and the fraction the partial week it belongs to. We will round up (Math.ceil)
		 * which will give us an integer number representing the week of the year the date is in.
		 */
		var interval = date.getTime() - (new Date(date.getFullYear(), 0, 0, 0, 0, 0, 0)).getTime();
		var currentWeek = Math.ceil(interval / 604800000);
		return 'Week ' + currentWeek + ' of ' + date.getFullYear();
		break;
	case '2week':
		var interval = date.getTime() - (new Date(date.getFullYear(), 0, 0, 0, 0, 0, 0)).getTime();
		var currentDoubleWeek = Math.ceil(interval / 1209600000);
		return 'Period ' + currentDoubleWeek + ' of ' + date.getFullYear();
		break;
	case 'month':
		var monthNames = monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August",
		        "September", "October", "November", "December" ];
		var monthName = monthNames[date.getMonth()];
		return monthName + ' ' + date.getFullYear();
		break;
	case 'quarter':
		// Decide quarter based on month
		var quarter = Math.ceil((date.getMonth() + 1) * (4 / 12));
		return quarter + '/4 - ' + date.getFullYear();
		break;
	case 'year':
		return date.getFullYear();
		break;
	case 'lifetime':
		return 'lifetime';
		break;
	default:
		break;
	}
}

/**
 * This function will search the object recursively for Series objects and returns an array of all that are found.
 * 
 * @param object {Object} any type of object whose properties will be recursively checked for series objects
 * @return {Array} list of SERIES objects
 */
function findAllSeries(object)
{
	var series = new Array();
	// Confirm the object is a real usable object so that the in iterator will work
	if (typeof object == 'object' && jQuery.isArray(object) == false && jQuery.isEmptyObject(object) == false)
	{
		for ( var property in object)
		{
			if (object[property] instanceof Series)
			{
				// Series found, add it.
				series.push(object[property]);
			} else
			{
				// It is not a series but it may contain series in it's own properties, search those as well. If it
				// contains
				// no series than the array will return with zero length
				var subSeries = findAllSeries(object[property]);
				if (subSeries.length > 0)
				{
					series = series.concat(subSeries);
				}
			}
		}
	}
	return series;
}

/**
 * @classDescription Abstracts a flot series with some special data we need for this file. Series object means this.
 *                   note: Make sure it's compatible, don't use flot property names unless you mean it. i.e. we're
 *                   sharing this with the flot plugin. I say this because it has a very dynamic set of properties,
 *                   essentialy just a combination of server and js input.
 * @constructor
 * @param {Object} [serverResponse] An object containing all data properties the sever function wishes to include. A
 *            'data' property is expected in this object and cooresponds to the flot data array.
 * @param {Object} [options] An object containing all data properties the called wishes to include.
 * @return {Series} [this] Returns the constructed object.
 */
function Series(serverResponse, options)
{
	this.visible = true;
	for (option in options)
	{
		this[option] = options[option];
	}
	for (serverOption in serverResponse.options)
	{
		this[serverOption] = serverResponse.options[serverOption];
	}
	this.data = serverResponse.data;
}

/*
 * UI utility functions
 */
/**
 * @return {Date,null} [from] From date from jQuery datepicker in DOM.
 */
function getFromDate()
{
	return jQuery('#from').datepicker("getDate");
}

/**
 * @return {Date,null} [to] To date from jQuery datepicker in DOM.
 */
function getToDate()
{
	return jQuery('#to').datepicker("getDate");
}

/**
 * @param {String} onChange
 * @param {String} request
 * @param {String} label
 * @param {jQuery element}element
 * @return
 */
function addCheckboxOption(onChange, request, label, element)
{
	return element.append('<div class="data-option"><input type="checkbox" onChange="' + onChange
	        + '" name="group-by" value="' + request + '" /> ' + label + '</div>');
}

/**
 * This utility function will copy any javascript object recursively to prevent referencing when a seperate value is
 * needed.
 * 
 * @param {Object} object
 * @return {Object} cloned object
 */
function clone(object)
{
	var newObj = (object instanceof Array) ? [] : {};
	for (i in object)
	{
		if (i == 'clone')
			continue;
		if (object[i] && typeof object[i] == "object")
		{
			newObj[i] = clone(object[i]);
		} else
			newObj[i] = object[i];
	}
	return newObj;
};

/**
 * Calls another function based on the status of a checkbox element. First argument must be boolean from :checked,
 * second may be the value of the checkbox
 * 
 * @param {jQuery element} jElement
 * @param {function} execute
 * @param {Product} product
 */
function checkboxShell(jElement, execute, product)
{
	try
	{
		if (jElement.is(':checked'))
		{
			var argument = true;
		} else
		{
			var argument = false;
		}
		var value = jElement.val();
		product[execute].call(argument, value);
	} catch (e)
	{
		throw "Failed to execute function from checkbox " + e;
	}
}

function downloadCsv()
{
	var csv = jQuery("#table-container table").table2CSV(
	{
		delivery : 'custom'
	});
	// newWindow = window.open(info.download_csv_url, 'data.csv');

	jQuery('#csvData').val(csv);
	jQuery('#csvForm').submit();
}

function csvCallback(csv)
{
	uriContent = "data:text/csv," + encodeURIComponent(csv);
}

jQuery(document).ready(
        function()
        {
	        // Add Sku autocomplete
	        jQuery('#newSku').autocomplete(
	        {
	            source : window.info.autocomplete,
	            minLength : 2,
	            select : function(event, ui)
	            {
		            event.preventDefault();
		            // abstract the product selected in js
		            new Product(ui.item.value);
	            }
	        });

	        var dates = jQuery("#from, #to").datepicker(
	                {
	                    defaultDate : "-1m",
	                    changeMonth : true,
	                    numberOfMonths : 1,
	                    onSelect : function(selectedDate)
	                    {
		                    var option = this.id == "from" ? "minDate" : "maxDate", instance = jQuery(this).data(
		                            "datepicker"), date = jQuery.datepicker.parseDate(instance.settings.dateFormat
		                            || jQuery.datepicker._defaults.dateFormat, selectedDate, instance.settings);
		                    dates.not(this).datepicker("option", option, date);

		                    graphData();
	                    }
	                });
	        jQuery('#from, #to').change(function()
	        {
		        graphData();
	        });
        });
