const API = (new function API() {
	let _domain	= null;
	let _secured	= true;
	
	this.setConnection = function setConnection(domain, secured) {
		_domain = domain;
		
		if(typeof(secured) !== 'undefined') {
			_secured = secured;
		}
	};
	
	this.getEndpoint = function getEndpoint() {
		return 'http' + (_secured ? 's' : '') + '://' + _domain + '/';
	};
	
	this.canAccess = function canAccess() {
		return KnuddelsServer.getExternalServerAccess().canAccessURL(this.getEndpoint());
	};
	
	this.call = function call(data, callback) {
		if(!this.canAccess()) {
			KnuddelsServer.getDefaultLogger().error('Can\'t access to Endpoint: ' + this.getEndpoint());
			return;
		}
		
		KnuddelsServer.getExternalServerAccess().postURL(this.getEndpoint(), {
			data: data,
			onSuccess: function onSuccess(body, response) {
				try {
					body = JSON.parse(body);
					callback(body.data);
				} catch(e) {
					callback(body);
				}
			},
			onFailure: function onFailure(body, response) {
				try {
					body = JSON.parse(body);
					callback(body.data);
				} catch(e) {
					callback(body);
				}
			}
		});
	};
	
	this.single = function single(statement, parameters, callback) {
		this.call({
			action:		'single',
			query:		statement,
			param:		JSON.stringify(parameters)
		}, callback);
	};
	
	this.fetch = function fetch(statement, parameters, callback) {
		this.call({
			action:		'fetch',
			query:		statement,
			param:		JSON.stringify(parameters)
		}, callback);
	};
	
	this.update = function update(table, reference, parameters, callback) {
		this.call({
			action:		'update',
			table:		table,
			where:		reference,
			param:		JSON.stringify(parameters)
		}, callback);
	};
	
	this.remove = function remove(table, parameters, callback) {
		this.call({
			action:		'remove',
			table:		table,
			param:		JSON.stringify(parameters)
		}, callback);
	};
	
	this.insert = function insert(table, parameters, callback) {
		this.call({
			action:		'insert',
			table:		table,
			param:		JSON.stringify(parameters)
		}, callback);
	};
	
	this.count = function count(statement, parameters, callback) {
		this.call({
			action:		'count',
			query:		statement,
			param:		JSON.stringify(parameters)
		}, callback);
	};
}());
