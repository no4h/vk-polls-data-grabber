// Hello
console.log("VK Polls data grabber!");

// Load modules
console.log("Loading modules...");

var config = require("./config");

var page = require("webpage").create();
page.settings.userAgent = config.data.userAgent;

console.log("Succeed!");

// Load index page
var indexPageUrl = "http://vk.com/";
console.log("Loading index page: '" + indexPageUrl + "' ...");

page.open(indexPageUrl, function (status) {

    console.log("Succeed!");

    // Check authorization


    phantom.exit();
});