Simple PNG generator
=============

This simple PHP script generates one RGB+Alpha colour PNGs for any given width and height. It also has base64 output.

The script first checks if the requested PNG already exists in the folder, if so, just loads the PNG from file; if it hasn't been made before, it generates it with GD, then stores the image for caching.

Installing
----------

You only need PHP 5 and above with GD support (which has been standard in PHP for a while now).

Just drop the `spng` folder somewhere in your project directory and you are done. I recommend droping inside the images folder.

Usage Instructions
------------------

From your browser or your code, just call the PNG you want in the following fashion. 

`spng/solid_0xRRGGBB[AA]_[width][x[Height]].png[&base64]`

So if you want a 32x32 red square: `spng/solid_0xFF0000_32x32.png`

Or if you want a 200x5 blue rectangle: `spng/solid_0x0000FF_200x5.png`

Or if you want a 1x1 green dot with 50% of transparency: `spng/solid_0x00FF0088.png`

Or if you want a 300x1 cyan line but as a *base64* string: `spng/solid_0x00FFFF_300.png&base64`

