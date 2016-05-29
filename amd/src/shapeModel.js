/**
 * Created by Stefan on 16.5.2016.
 */
define( function($) {
    return function(opts) {
        // konstruktor
        this.name = opts.name;
        this.id = opts.id;
        this.x3d = opts.x3d;
        this.inScene=false;
        if((typeof (opts.idForResponse)) !== 'undefined') { // shape koji je u sceni (ima koordinate)
            this.x= opts.x;
            this.y= opts.y;
            this.z= opts.z;
            this.idForResponse= opts.idForResponse;
            inScene=true;
        }
    };
});