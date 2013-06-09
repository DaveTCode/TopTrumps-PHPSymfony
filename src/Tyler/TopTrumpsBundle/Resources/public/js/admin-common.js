TopTrumpsCommon = {
    /**
     * Take a file input box referred to by the source selector and (using
     * html5 file readers) load the image into a destination img tag.
     *
     * @param srcSelector - Must refer to a file type input
     * @param destSelector - Must refer to an img.
     */
    loadImageBytes: function(srcSelector, destSelector) {
        var reader = new FileReader();
        var image = $(srcSelector)[0].files[0];
        reader.onload = (function(imageFile) {
            return function(event) {
                $(destSelector).attr("src", event.target.result);
            };
        })(image);
        reader.readAsDataURL(image);
    }
}