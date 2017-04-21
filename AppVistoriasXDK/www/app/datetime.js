
/** 
 * 
 */
function timestampUTC() {
    //var today = new Date();
    //return Date.UTC(today.getFullYear(), today.getMonth(), today.getDate(), today.getHours(), today.getMinutes(), today.getSeconds(), today.getMilliseconds);
    return (Date.now()/1000) | 0;
}

/** 
 * 
 */
function calcTime(timestamp, offset) {

    // create Date object for current location
    var d = new Date(timestamp);

    // convert to msec
    // add local time zone offset
    // get UTC time in msec
    var utc = d.getTime() + (d.getTimezoneOffset() * 60000);

    // create new Date object for different city
    // using supplied offset
    var nd = new Date(utc + (3600000*offset));

    // return time as a string
    return "The local time is " + nd.toLocaleString();
}

/** 
 * 
 */
function convertToLocalTime(timestamp) {

    // create Date object for current location
    var d = new Date(timestamp);

    return d.toLocaleString();
}