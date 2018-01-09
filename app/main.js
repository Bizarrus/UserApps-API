require('classes/API.class.js');

var App = (new function AppContainer() {
	this.onAppStart = function onAppStart() {
		API.setConnection('api.domain.tld', true);
	};
	
	this.chatCommands = {
		QuerySingle: function QuerySingle(user, params) {
			API.single('SELECT * FROM `tickets` WHERE `id`=:id', {
				id: 1
			}, function onSuccess(data) {
				user.sendPrivateMessage(JSON.stringify(data, 0, 1).escapeKCode().replace(/\n/gi, '°#°'));
			});
		},
		QueryFetch: function QueryFetch(user, params) {
			API.fetch('SELECT * FROM `tickets`', {}, function onSuccess(data) {
				user.sendPrivateMessage(JSON.stringify(data, 0, 1).escapeKCode().replace(/\n/gi, '°#°'));
			});
		},
		QueryCount: function QueryCount(user, params) {
			API.count('SELECT * FROM `tickets`', {}, function onSuccess(data) {
				user.sendPrivateMessage(JSON.stringify(data, 0, 1).escapeKCode().replace(/\n/gi, '°#°'));
			});
		},
		QueryUpdate: function QueryUpdate(user, params) {
			API.update('tickets', 'id', {
				id:		2,
				user_id:	98765
			}, function onSuccess(data) {
				user.sendPrivateMessage(JSON.stringify(data, 0, 1).escapeKCode().replace(/\n/gi, '°#°'));
			});
		},
		QueryRemove: function QueryRemove(user, params) {
			API.remove('tickets', {
				id:		2,
			}, function onSuccess(data) {
				user.sendPrivateMessage(JSON.stringify(data, 0, 1).escapeKCode().replace(/\n/gi, '°#°'));
			});
		},
		QueryInsert: function QueryInsert(user, params) {
			API.insert('tickets', {
				id:		null,
				user_id:	1337,
				numer:		999,
				time_created:	'NOW()'
			}, function onSuccess(data) {
				user.sendPrivateMessage(JSON.stringify(data, 0, 1).escapeKCode().replace(/\n/gi, '°#°'));
			});
		}
	};
}());
