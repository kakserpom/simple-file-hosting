var routes = require('../Routes.cfg');
var routeException = function (message) {
    this.message = message;
};
const jQuery = $ = require('jquery');
var exports = module.exports = {
    url: function (routeName, params, query) {
        if (typeof params === 'string') {
            params = {action: params};
        }

        var route = routes[routeName] || null;
        if (!route) {
            throw new routeException('Route not found');
        }
        route.defaults = route.defaults || {};
        params = params || {};
        var url = route.path.replace(/\{(.*?)\}/g, function (match, key) {
            return params[key] || route.defaults[key] || '';
        });
        if (query) {
            url += '?' + $.param(query);
        }
        return url;
    }
};
