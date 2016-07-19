# ERRORES
## Problemas con CORS

Chrome lanza una peticion OPTIONS antes de la petición POST, si el servicio web no acepta OPTIONS este falla.
http://stackoverflow.com/questions/33660712/angularjs-post-fails-response-for-preflight-has-invalid-http-status-code-404

***

## Problema con Angular, $http y enviar datos por post

http://stackoverflow.com/questions/19254029/angularjs-http-post-does-not-send-data

Utiliza un Content-Type application/json en vez de x-www-form-urlencoded. Por lo que los parámetros los envia con un json escritos en el body de la petición.

Desde angular, para prevenir esto:

```javascript
app.config(['$httpProvider', function ($httpProvider) {
      
        $httpProvider.defaults.headers.common = {};
        $httpProvider.defaults.headers.post = {
            'Content-Type': 'application/json;charset=utf-8'
        };
        $httpProvider.defaults.headers.put = {};
        $httpProvider.defaults.headers.patch = {};
		/**
		 * The workhorse; converts an object to x-www-form-urlencoded serialization.
		 * @param {Object} obj
		 * @return {String}
		 */
		var param = function (obj) {
			var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

			for (name in obj) {
				value = obj[name];

				if (value instanceof Array) {
					for (i = 0; i < value.length; ++i) {
						subValue = value[i];
						fullSubName = name + '[' + i + ']';
						innerObj = {};
						innerObj[fullSubName] = subValue;
						query += param(innerObj) + '&';
					}
				} else if (value instanceof Object) {
					for (subName in value) {
						subValue = value[subName];
						fullSubName = name + '[' + subName + ']';
						innerObj = {};
						innerObj[fullSubName] = subValue;
						query += param(innerObj) + '&';
					}
				} else if (value !== undefined && value !== null)
					query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
			}
			return query.length ? query.substr(0, query.length - 1) : query;
		};

		// Override $http service's default transformRequest
		$httpProvider.defaults.transformRequest = [function (data) {
				return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
			}];
       
    }]);
```
### Solución

Modificar desde lado del servidor para que acepte application/json y parsear los parámetros leyendo el body del request

***

