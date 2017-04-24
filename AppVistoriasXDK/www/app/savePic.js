var pictureSource;   // picture source
var destinationType; // sets the format of returned value 
// Wait for PhoneGap to connect with the device
//
document.addEventListener("deviceready",onDeviceReady,false);
// PhoneGap is ready to be used!
//
function onDeviceReady() {
    pictureSource=navigator.camera.PictureSourceType;
    destinationType=navigator.camera.DestinationType;
}
// Called when a photo is successfully retrieved
//
function onPhotoDataSuccess(imageData) {
  // Get image handle
  //
  var smallImage = document.getElementById('smallImage');
  // Unhide image elements
  //
  smallImage.style.display = 'block';
  // Show the captured photo
  // The inline CSS rules are used to resize the image
  //
  smallImage.src = "data:image/jpeg;base64," + imageData;
}

// Called when a photo is successfully retrieved
//
function onPhotoFileSuccess(imageData) {
  // Get image handle
  console.log(JSON.stringify(imageData));

  // Get image handle
  //
  var smallImage = document.getElementById('smallImage');
  // Unhide image elements
  //
  smallImage.style.display = 'block';
  // Show the captured photo
  // The inline CSS rules are used to resize the image
  //
  smallImage.src = imageData;
}
// Called when a photo is successfully retrieved
//
function onPhotoURISuccess(imageURI) {
  // Uncomment to view the image file URI 
  // console.log(imageURI);
  // Get image handle
  //
  var largeImage = document.getElementById('largeImage');
  // Unhide image elements
  //
  largeImage.style.display = 'block';
  // Show the captured photo
  // The inline CSS rules are used to resize the image
  //
  largeImage.src = imageURI;
}
// A button will call this function
//
function capturePhotoWithData() {
  // Take picture using device camera and retrieve image as base64-encoded string
  navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 50 });
}
function ccapturePhotoWithFile() {
    navigator.camera.getPicture(onPhotoFileSuccess, onFail, { quality: 50, destinationType: Camera.DestinationType.FILE_URI });
}

// A button will call this function
//
function getPhoto(source) {
  // Retrieve image file location from specified source
  navigator.camera.getPicture(onPhotoURISuccess, onFail, { quality: 50, 
    destinationType: destinationType.FILE_URI,
    sourceType: source });
}
// Called if something bad happens.
// 
function onFail(message) {
  alert('Failed because: ' + message);
}

function moveFile(fileUri) {
    console.log(fileUri);
    window.resolveLocalFileSystemURL(
          fileUri,
          function(fileEntry)
          {
                newFileUri  = cordova.file.dataDirectory + "images/";
                oldFileUri  = fileUri;
                fileExt     = "." + oldFileUri.split('.').pop();

                newFileName = 'car' + fileExt;
                window.resolveLocalFileSystemURL(newFileUri,
                        function(dirEntry)
                        {
                            // move the file to a new directory and rename it
                            fileEntry.moveTo(dirEntry, newFileName, successCallback, errorCallback);
                            return newFileUri + newFileName;
                        },
                        photoErrorCallback);
          },
          photoErrorCallback);
}

function gotFile(file){
    readDataUrl(file);
}

function readDataUrl(file) {
    var reader = new FileReader();
    reader.onloadend = function(evt) {
        console.log("Read as data URL");
        console.log(evt.target.result);

        var imageData = evt.target.result;
        return imageData;
    };  
    reader.readAsDataURL(file); // Read as Data URL (base64)
}

function photoErrorCallback()
{
    return false;
}