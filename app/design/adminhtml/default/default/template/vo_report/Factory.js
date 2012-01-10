var generation = 0;
var factories = new Array();
var widgets = 0;

function Factory()
{
	this.generationCreated = generation;
	factories.push(this);
}

Factory.prototype.buildFactory = function()
{
	new Factory();
};

Factory.prototype.buildWidget = function()
{
	widgets++;
};

// Advance one generation defining the 0-1 % of factories should make new factories
function advanceGeneration(ratioOfFactories)
{
	generation++;
	var numNewFactories = Math.round(ratioOfFactories * factories.length);

	for ( var f = 0; f < numNewFactories; f++)
	{
		factories[f].buildFactory();
	}
	for ( var w = f; o < factories.length; w++)
	{
		factories[w].buildWidget();
	}
}

function simulate()
{
	var N = 2;
	var k = 100;
	// starting N
	for (n = 0; n < N; n++)
	{
		new Factory();
	}
	
	while(generation < k)
	{
		//Insert logic for various strategies here.
		advanceGeneration(0.5);
	}
}