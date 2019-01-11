var page = require('webpage').create(),
	address, output, size;
var system = require('system');	
address = system.args[1];
output = system.args[2];
page.open(address, function (status) {
    if (status !== 'success') {
        console.log('Unable to load the address!');
    } else {
        window.setTimeout(function () {
            page.render(output);
            phantom.exit();
        }, 200);
    }
});
