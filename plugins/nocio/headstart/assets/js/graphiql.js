let loaded = false;

// Extract "GET" parameters from a JS include querystring
function getParams(script_name) {
  // Find all script tags
  var scripts = document.getElementsByTagName("script");

  // Look through them trying to find ourselves
  for(var i=0; i<scripts.length; i++) {
    if(scripts[i].src.indexOf("/" + script_name) > -1) {
      // Get an array of key=value strings of params
      var pa = scripts[i].src.split("?").pop().split("&");

      // Split each key=value into array, the construct js object
      var p = {};
      for(var j=0; j<pa.length; j++) {
        var kv = pa[j].split("=");
        p[kv[0]] = kv[1];
      }
      return p;
    }
  }

  // No scripts match
  return {};
}

function lazyAddScript(filename) {
  var head = document.getElementsByTagName('head')[0];

  var script = document.createElement('script');
  script.src = filename;
  script.type = 'text/javascript';

  head.insertBefore(script, document.getElementsByTagName("script")[0]);
}

function lazyAddCSS(filename) {
  var head = document.getElementsByTagName('head')[0];

  var style = document.createElement('link');
  style.href = filename;
  style.type = 'text/css';
  style.rel = 'stylesheet';
  head.appendChild(style);
}

function safeSerialize(data) {
  return data ? JSON.stringify(data).replace(/\//g, '\\/') : null;
}
// Collect the URL parameters
var parameters = {};
window.location.search.substr(1).split('&').forEach(function (entry) {
  var eq = entry.indexOf('=');
  if (eq >= 0) {
    parameters[decodeURIComponent(entry.slice(0, eq))] =
      decodeURIComponent(entry.slice(eq + 1));
  }
});
// Produce a Location query string from a parameter object.
function locationQuery(params, location) {
  if (!params || Object.keys(params).length === 0) {
      return location ? location: ''
  }
  return (location ? location: '') + '?' + Object.keys(params).map(function (key) {
    return encodeURIComponent(key) + '=' +
      encodeURIComponent(params[key]);
  }).join('&');
}
// Derive a fetch URL from the current URL, sans the GraphQL parameters.
var graphqlParamNames = {
  query: true,
  variables: true,
  operationName: true
};
var otherParams = {};
for (var k in parameters) {
  if (parameters.hasOwnProperty(k) && graphqlParamNames[k] !== true) {
    otherParams[k] = parameters[k];
  }
}
// We don't use safe-serialize for location, becuase it's not client input.
//    var fetchURL = locationQuery(otherParams, '${endpointURL}');
var fetchURL = locationQuery(otherParams, getParams('graphiql.js').fetchUrl || '/graphql');
if (!fetchURL.endsWith('/')) {
    fetchURL = fetchURL + '/';
}
// Defines a GraphQL fetcher using the fetch API.
function graphQLFetcher(graphQLParams) {
  return fetch(fetchURL, {
    method: 'post',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(graphQLParams),
    credentials: 'include',
  }).then(function (response) {
    return response.text();
  }).then(function (responseBody) {
    try {
      return JSON.parse(responseBody);
    } catch (error) {
      return responseBody;
    }
  });
}
// When the query and variables string is edited, update the URL bar so
// that it can be easily shared.
function onEditQuery(newQuery) {
  parameters.query = newQuery;
  updateURL();
}
function onEditVariables(newVariables) {
  parameters.variables = newVariables;
  updateURL();
}
function onEditOperationName(newOperationName) {
  parameters.operationName = newOperationName;
  updateURL();
}
function updateURL() {
  // history.replaceState(null, null, locationQuery(parameters));
}

function loadGraphiQL() {
  if (!loaded) {
    lazyAddScript("https://unpkg.com/whatwg-fetch@3.0.0/dist/fetch.umd.js");
    lazyAddScript("https://unpkg.com/react@16.8.6/umd/react.production.min.js");
    lazyAddCSS("https://unpkg.com/graphiql@0.13.0/graphiql.css");
    lazyAddScript("https://unpkg.com/graphiql@0.13.0/graphiql.min.js")
    lazyAddScript("https://unpkg.com/react-dom@16.8.6/umd/react-dom.production.min.js");
  }

  let tabId = 'graphiql' + Math.random()

  $('#cms-master-tabs').ocTab(
    'addTab',
    'Test',
    '<div></div><div id="' + tabId + '" class="graphiql loading-indicator-container"><div class="loading-indicator indicator-center"><span></span></div></div></div>',
    tabId,
    'oc-icon-play-circle'
  )
  $('#layout-side-panel').trigger('close.oc.sidePanel')

  function loadWhenReady(){
    if(typeof ReactDOM !== "undefined" && typeof GraphiQL !== 'undefined' && typeof React !== 'undefined') {
      // Render <GraphiQL /> into the body.
      ReactDOM.render(
        React.createElement(GraphiQL, {
          fetcher: graphQLFetcher,
          onEditQuery: onEditQuery,
          onEditVariables: onEditVariables,
          onEditOperationName: onEditOperationName,
          query: parameters.query,
          response: safeSerialize(null), //${safeSerialize(resultString)},
          variables: parameters.variables || "{}", //"{}",//${safeSerialize(variablesString)},
          operationName: parameters.operationName === 'null' ? null: parameters.operationName
        }),
        document.getElementById(tabId)
      );
    }
    else{
      setTimeout(loadWhenReady, 250);
    }
  }

  loadWhenReady();

  loaded = true;
}

