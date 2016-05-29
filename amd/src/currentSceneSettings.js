/**
 * Created by Stefan on 16.5.2016.
 */
define([ 'jquery' , 'x3dom' ], function( $, x3dom ) {
    return x = {
        $shapesLoading: $("#shapesLoading"),
        $sceneLoading: $("#sceneLoading"),
        $saveSceneButton: $('#saveScene'),
        $selScena: $('#izabranascena'),
        $shapeAll: $('#shapesAll'),
        init: function() {
            x.sceneLoadingStart();x.shapesAllLoadingStart();
            x.loadSceneAndShapes();
            x.bindEvents();
        },
        sceneLoadingStart: function() {
            x.$sceneLoading.show();
            x.$saveSceneButton.hide();
            x.$shapeAll.hide();
        },
        sceneLoadingStop: function() {
            x.$sceneLoading.hide();
            x.$saveSceneButton.show();
        },
        shapesAllLoadingStart: function() {
            x.$shapeAll.hide();
        },
        shapesAllLoadingStop: function() {
            x.$shapeAll.show()
        },
        loadSceneAndShapes: function() {
            require(['core/ajax'], function (ajax) {
                var promises = ajax.call([
                    {methodname: 'getSceneX3d', args: {sceneid: x.$selScena.val()}},
                    {methodname: 'getPosAnsForScene', args: {}}
                ]);
                promises[0].done(x.setScene(response)).fail(function (ex) {
                    console.log(ex);
                });
                promises[1].done(x.setShapesAll(response)).fail(function (ex) {
                    console.log(ex);
                });
            });
        },
        setScene: function(response) {
            x.sceneLoadingStop();
            var r=$(response);
            if(r.find('navigationInfo').length!=0)
                r.find('navigationInfo').attr('id','navInfo');
            else
                r.find('scene').append('<navigationInfo id="navInfo"></navigationInfo>');
            r.attr('onmouseup','stopDragging()');
            r.attr('onmousemove','mouseMoved(event)');
            r.attr('id','someUniqueId');
            $.each(r.find('transform'),function(index, value) {
                if(typeof $(value).attr('def') !== 'undefined')
                    $(value).attr('onmousedown','startDragging(this,event);').attr('onclick','shapeContextMenu(this,event);');
            });
            $('#scena').empty().append(r);
            x3dom.reload();
        },
        setShapesAll: function(response) {
            x.shapesAllLoadingStop();
            $.each(response, function (index, value) {
                var x3d_tag=$("<x3d class='x3d_shape' showStat='false' showLog='false' width='150px' height='80px'><scene><navigationInfo type='\"\"'></navigationInfo>"+value.shapexdom+"</scene></x3d>");
                x3d_tag.find("transform").attr('shapeId',value.id);
                x3d_tag.find("transform").attr('shapeName',value.name);
                var shapeWraper=$("<li class='shapeWraper' id='"+value.id+"'></li>");
                var shapeName=$("<div class='shapeName'>"+value.name+"</div>");
                var shapeScene=$("<div class='shapeScene'></div>");
                x3d_tag.appendTo(shapeScene);
                shapeName.appendTo(shapeWraper);
                shapeScene.appendTo(shapeWraper);
                shapeWraper.appendTo(x.$shapeAll);
            });
            x3dom.reload();
        },
        bindEvents: function() { // TO DO
            $('#saveScene').button().unbind().click(function(event) {
                $('#saveScene').hide();
                $('#sceneLoading').show();
                var r=$("#someUniqueId"); var newShapes=[];
                $.each(r.find('transform'),function(index, value) {
                    var shapeid;
                    if(typeof (shapeid=$(value).attr('shapeid')) !== 'undefined') { // ovo je da bih uzeo samo stvarne shape-ove
                        var cord=$(value).attr('translation').split(' ');
                        newShapes.push({
                            shapeid:shapeid,
                            shapeCords: {
                                x:cord[0],
                                y:cord[1],
                                z:cord[2]
                            }
                        });
                    }
                });
                require(['core/ajax'], function (ajax) {
                    var promises = ajax.call([
                        {methodname: 'saveNewShapesOnScene', args: {sceneid: selScena.val(),newShapes: newShapes}}
                    ]);
                    promises[0].done(function (response) {
                        console.log('Done',response);
                        $('#saveScene').show();
                        $('#sceneLoading').hide();
                        alert('Scena je sacuvana.');
                    }).fail(function (ex) {
                        $(".selector").show();
                        $('#sceneLoading').hide();
                        alert('Doslo je do greske. Scena nije sacuvana.');
                        console.log(ex);
                    });
                });
            });
            $(".shapeWraper").draggable({
                revert:true,
                containment:"#draggableArea",
                scroll:false,
                helper: 'clone'
            });
            $("#scena").droppable({
                drop: function(event, ui) {
                    var root_scene=$("div#scena>x3d");
                    var root_scene_html=root_scene.get(0);
                    var child_scene=ui.draggable;
                    var runtime=root_scene_html.runtime;
                    var pos=runtime.mousePosition(event);
                    var vr=runtime.getViewingRay(pos[0],pos[1]);
                    var pos_3d = vr.pos.add(vr.dir.multiply(4));
                    var copy_child=child_scene.clone();
                    copy_child.find('transform').attr('translation',pos_3d.x+' '+pos_3d.y+' '+pos_3d.z).attr('onmousedown','startDragging(this,event);').attr('onclick','shapeContextMenu(this,event);');
                    root_scene.find('scene').append(copy_child.find('transform'));
                }
            });
            $.contextMenu({
                selector: '.hasmenu',
                build: function($triggerElement, e){
                    return {
                        callback: function(key,opt){
                            switch(key) {
                                case 'delete':
                                    $(e.shape).remove();
                                    break;
                                default:
                            }
                        }
                    };
                },
                items: {
                    "delete": {name: "Izbrisi", icon: "delete"}
                }
            });

        },
        saveCurrentScene: function() {

        }
    }
});