var page = require('webpage').create();
var server = require('webserver').create();
var system = require('system');
var host, port, source;

if (system.args.length !== 2) {
    console.log('Usage: server.js <some port>');
    phantom.exit(1);
} else {
    port = system.args[1];
    var listening = server.listen(port, function (request, response) {
        if (request.url.match(/favicon.ico$/i)) {
            console.log('ignoring favicon');
            return;
        }

        console.log("GOT HTTP REQUEST");
        console.log(JSON.stringify(request, null, 4));

        source = 'http://' + request.url.replace(/^\//, '').replace('%3A', ':');
        console.log('requesting url: ' + source);

        response.statusCode = 200;
        response.headers = {"Cache": "no-cache", "Content-Type": "application/json"};

        page.onConsoleMessage = function (msg) {
            console.log(msg);
        };

        page.open(source, function (status) {
            if (status !== 'success') {
                console.log('Unable to access ' + source);
                response.write(JSON.stringify({
                    error: 'Unable to access ' + source
                }));
            } else {
                var html = page.evaluate(function () {
                    console.log('page evaluated');
                    return document.getElementsByTagName('html')[0].innerHTML
                });

                response.write(JSON.stringify({
                    source: html
                }));
            }
            response.close();
        });
    });
    if (!listening) {
        console.log("could not create web server listening on port " + port);
        phantom.exit();
    } else {
        console.log("listening on port " + port);
    }
}