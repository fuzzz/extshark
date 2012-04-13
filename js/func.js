
Ext.BLANK_IMAGE_URL = './lib/ext/resources/images/default/s.gif';
Ext.Direct.addProvider(Ext.app.REMOTING_API);
Ext.Loader.setConfig({enabled: true});

/* Usefull to debug */
function showObj(obj, objName){
    var result = "";
    for (var i in obj) result += objName + "." + i + " = " + obj[i] + "<br />\n";
    return result;
}