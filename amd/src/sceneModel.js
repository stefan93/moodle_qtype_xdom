/**
 * Created by Stefan on 16.5.2016.
 */
define([ 'qtype_xdom/shapeModel' ], function(Shape,$) {
    var x=function(opts) {
        // konstruktor
        this.name = opts.name || "";
        this.id = opts.id || "";
        this.x3d = opts.x3d || "";
        var _shapes = [];
        opts.shapes.forEach(function(item, index) {
            _shapes.push(new Shape(item));
        });
        this.shapes = _shapes;
    };
    x.prototype.addShape = function(newShape) {
        this.shapes.push(newShape);
    };
    x.prototype.removeShape = function(rmShape) {
        var index = this.shapes.indexOf(rmShape);
        this.shapes.pop(index);
    };
    x.prototype.moveShapeAt = function(shape,x,y,z) {
        var index = this.shapes.indexOf(shape);
        this.shapes[index].x=x;
        this.shapes[index].y=y;
        this.shapes[index].z=z;
    }
});