// Standard license block omitted.
/*
 * @package    qtype_xdom
 * @copyright  2015 Someone cool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module qtype_xdom/xdommodule
 */
require.config({
    enforceDefine: false,
    paths: {
        "x3dom": [
           // "http://www.x3dom.org/x3dom/release/x3dom",
            "http://localhost/moodle/question/type/xdom/lib/x3dom"
        ],
        "jquery-ui/menu": [
            "http://localhost/moodle/question/type/xdom/lib/jquery.contextMenu"
        ],
        "jquery.datatables": [
            "http://localhost/moodle/question/type/xdom/lib/datatables.min"
        ]
    },
    shim: {
        'x3dom': {
            exports: 'x3dom'
        }
    }
});
define(['x3dom','jquery','jqueryui','jquery-ui/menu'], function(x3dom,$) {
    function roundWithTwoDecimals(value)
    {
        return (Math.round(value * 100)) / 100;
    }
    function handleGroupClick(event)
    {
        //Mark hitting point
        $('#marker').attr('translation', event.hitPnt);

        //Display coordinates of hitting point (rounded)
        var coordinates = event.hitPnt;
        if ($('#id_xcord').length) {
            $('#id_xcord').val(roundWithTwoDecimals(coordinates[0]));
            $('#id_ycord').val(roundWithTwoDecimals(coordinates[1]));
            $('#id_zcord').val(roundWithTwoDecimals(coordinates[2]));
        } else {
            $('#xcord').val(roundWithTwoDecimals(coordinates[0]));
            $('#ycord').val(roundWithTwoDecimals(coordinates[1]));
            $('#zcord').val(roundWithTwoDecimals(coordinates[2]));
        }
    }
    function handleSingleClick(shape) {
        if ($("#id_choosenshape").length) {
            $("#id_choosenshape").val($(shape).attr("def"));
        } else {
            $('#choosenshape').val($(shape).attr("def"));
        }
    }
    function getCoords(xKoor, yKoor, zKoor, struk){
        struk.x = xKoor;
        struk.y = yKoor;
        struk.z = zKoor;
    }

    function sendCoords(evt){
        if(evt){
            var coordinates = {
                x:'xKoor',
                y:'yKoor',
                z:'zKoor'
            };
            var pos = evt.position;
            var x = pos.x.toFixed(4);
            var y = pos.y.toFixed(4);
            var z = pos.z.toFixed(4);
            getCoords(x,y,z,coordinates);
            tekst=JSON.stringify(coordinates);
            //kod za slanje
            require(['core/ajax'], function (ajax) {
                var promises = ajax.call([
                    {methodname: 'saveCoords', args: {x: x, y: y,  z: z }}
                ]);
                promises[0].done(function (response) {
                    console.log('Done',response);
                }).fail(function (ex) {
                    alert('Doslo je do greske. Scena nije sacuvana.');
                    console.log(ex);
                });
            });
        }
    }
    var x={
        init : function() {
            window.onload = function() {


                var scene=$("Scene");
                scene.attr('onclick', 'handleGroupClick(event)');

                scene.append($("<viewpoint orientation='-0.21 0.97 0.0644 0.59193' position='45.15 0.42 5.11813'></viewpoint>"));

                var viewpointNode = document.getElementsByTagName('viewpoint')[0];
                viewpointNode.addEventListener('viewpointChanged',sendCoords, false);

                window.handleGroupClick = handleGroupClick;
                $("transform").each(function() {
                    $(this).attr("onclick", "handleSingleClick(this)");
                });
                window.handleSingleClick = handleSingleClick;
                if (!$('#marker').length) {
                    scene.append('<Transform id="marker" scale=".15 .15 .15" translation="100 0 0"> <Shape><Appearance><Material diffuseColor="#FFD966"></Material></Appearance><Sphere></Sphere></Shape></Transform>');
                }
                window.getCoords = getCoords;
                window.sendCoords = sendCoords;

        };
        },
        ui : function() {
            document.onload = function() {
                $("#dialog").dialog();
            }
        },
        edit_form : function() {
            $(document).ready(function () {
                var selScena=$('#izabranascena');
                var selOdgovor=$('#izabranodgovor');
                var tabs=$("#tabs");
                tabs.tabs({
                    create:function() {
                        tabs.show();
                        $("#tabsloading").hide();
                    },
                    beforeActivate:function(event,ui) {
                        switch(ui.newPanel.selector) {
                            case "#fragment-2":
                                $("#scena").empty();
                                var shapesAll = $('#shapesAll').empty();
                                $("#sceneLoading").show();
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
                                $('#saveScene').hide();
                                $('#shapesAll').hide();
                                require(['core/ajax'], function (ajax) {
                                    var promises = ajax.call([
                                        {methodname: 'getSceneX3d', args: {sceneid: selScena.val()}},
                                        {methodname: 'getPosAnsForScene', args: {}}
                                    ]);
                                    promises[0].done(function (response) {
                                        $("#sceneLoading").hide();
                                        $('#saveScene').show();
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
                                        //x3dom.reload();
                                    }).fail(function (ex) {
                                        console.log(ex);
                                    });
                                    promises[1].done(function (response) {
                                        $('#shapesAll').show();
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
                                            shapeWraper.appendTo(shapesAll);
                                        });
                                        x3dom.reload();
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
                                    }).fail(function (ex) {
                                        console.log(ex);
                                    });
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

                                break;
                            case '#fragment-1':
                                var trazenaScena = selScena.val();
                                selOdgovor.hide();
                                $("#shapesLoading").show();
                                selOdgovor.empty();
                                require(['core/ajax'], function (ajax) {
                                    var promises = ajax.call([
                                        {
                                            methodname: 'getPosAnsForScene',
                                            args: {sceneid: trazenaScena}
                                        }
                                    ]);
                                    promises[0].done(function (response) {
                                        $("#shapesLoading").hide();
                                        selOdgovor.show();
                                        cacheForShapes.add(response, trazenaScena);
                                        $.each(response, function (index, value) {
                                            selOdgovor.append("<option value='" + value.id + "'>" + value.name + "</option>");
                                        });
                                        correctshape.val(selOdgovor.val());
                                    }).fail(function (ex) {
                                        console.log(ex);
                                    });
                                });
                                break;
                            case '#fragment-3':
                                require(['core/ajax'], function (ajax) {
                                    var scenesDiv = $("#scenes");
                                    var shapesDiv = $("#shapes");
                                    var bScenesDiv = $("#backgroundScenes");
                                    var fr3Content = $("#fragment-3-content");
                                    //fr3Content.hide();
                                    var promises = ajax.call([
                                        {
                                            methodname: 'qtypeManagment',
                                            args: {type: 'get_all_scenes'}
                                        },
                                        {
                                            methodname: 'qtypeManagment',
                                            args: {type: 'get_all_shapes'}
                                        },
                                        {
                                            methodname: 'qtypeManagment',
                                            args: {type: 'get_all_backgroundscenes'}
                                        }
                                    ]);
                                    require(['jquery.datatables'], function() {
                                        var table = $('#tableScenes').dataTable( {
                                            "searching": false,
                                            "ajax": function(data, callback, settings) {
                                                promises[1].done(function (response) {
                                                    response.data = Object.keys(response.data).map(function (key) {return response.data[key]});
                                                    callback(response);
                                                }).fail(function (ex) {
                                                    alert("Doslo je do greske.");
                                                    console.log(ex);
                                                });
                                            },
                                            "lengthChange": false,
                                            "columns": [
                                                {
                                                    "sortable": true,
                                                    "title": 'Naziv',
                                                    render: function(o) { return o; }
                                                },
                                                {
                                                    "sortable": false,
                                                    render: function (o) { return '<div class="btn" onclick="sceneEditById(this)" data-role="scene-delete-by-id" data-id="'+o+'">Izmeni</div>'; }
                                                },
                                                {
                                                    "sortable": false,
                                                    render: function (o) { return '<div class="btn" onclick="sceneDelById(this)" data-role="scene-delete-by-id" data-id="'+o+'">Izbrisi</div>'; }
                                                }
                                            ]
                                        } );
                                    });
                                    /*
                                    promises[0].done(function (response) {
                                        var dataScenes = [];
                                        $.map(response.rows, function(item, index) {
                                            dataScenes.push([item,index,index]);
                                        });
                                        require(['jquery.datatables'], function() {
                                            var table = $('#tableScenes').dataTable( {
                                                "searching": false,
                                                "data": dataScenes,
                                                "lengthChange": false,
                                                "columns": [
                                                    {
                                                        "sortable": true,
                                                        "title": 'Naziv',
                                                        render: function(o) { return o; }
                                                    },
                                                    {
                                                        "sortable": false,
                                                        render: function (o) { return '<div class="btn" onclick="sceneEditById(this)" data-role="scene-delete-by-id" data-id="'+o+'">Izmeni</div>'; }
                                                    },
                                                    {
                                                        "sortable": false,
                                                        render: function (o) { return '<div class="btn" onclick="sceneDelById(this)" data-role="scene-delete-by-id" data-id="'+o+'">Izbrisi</div>'; }
                                                    }
                                                ]
                                            } );
                                        });
                                    }).fail(function (ex) {
                                        alert("Doslo je do greske.");
                                        console.log(ex);
                                    });
                                    promises[1].done(function(response) {
                                        var dataShapes = [];
                                        $.map(response.rows, function(item, index) {
                                            dataShapes.push([item,index,index]);
                                        });
                                        require(['jquery.datatables'], function() {
                                            var table = $('#tableShapes').dataTable( {
                                                "searching": false,
                                                "data": dataShapes,
                                                "lengthChange": false,
                                                "columns": [
                                                    {
                                                        "sortable": true,
                                                        "title": 'Naziv',
                                                        render: function(o) { return o; }
                                                    },
                                                    {
                                                        "sortable": false,
                                                        render: function (o) { return '<div class="btn" onclick="shapeEditById(this)" data-role="shape-delete-by-id" data-id="'+o+'">Izmeni</div>'; }
                                                    },
                                                    {
                                                        "sortable": false,
                                                        render: function (o) { return '<div class="btn" onclick="shapeDelById(this)" data-role="shape-delete-by-id" data-id="'+o+'">Izbrisi</div>'; }
                                                    }
                                                ]
                                            } );
                                        });
                                    }).fail(function(ex) {
                                        alert("Doslo je do greske.");
                                        console.log(ex);
                                    });

                                    promises[2].done(function(response) {
                                        var dataBScenes = [];
                                        $.map(response.rows, function(item, index) {
                                            dataBScenes.push([item,index,index]);
                                        });
                                        require(['jquery.datatables'], function() {
                                            var table = $('#tableBScenes').dataTable( {
                                                "searching": false,
                                                "data": dataBScenes,
                                                "lengthChange": false,
                                                "columns": [
                                                    {
                                                        "sortable": true,
                                                        "title": 'Naziv',
                                                        render: function(o) { return o; }
                                                    },
                                                    {
                                                        "sortable": false,
                                                        render: function (o) { return '<div class="btn" onclick="bSceneEditById(this)" data-role="bScene-delete-by-id" data-id="'+o+'">Izmeni</div>'; }
                                                    },
                                                    {
                                                        "sortable": false,
                                                        render: function (o) { return '<div class="btn" onclick="bSceneDelById(this)" data-role="bScene-delete-by-id" data-id="'+o+'">Izbrisi</div>'; }
                                                    }
                                                ]
                                            } );
                                        });
                                        fr3Content.show();
                                    }).fail(function(ex) {
                                        alert("Doslo je do greske.");
                                        console.log(ex);
                                    });*/
                                });
                                break;
                            default :
                        }
                    }
                });
            var sceneid=$("[name=sceneid]");
            var correctshape=$("[name=correctshape]");
            var cacheShapes =function() {
                this.cache = [];
                this.add = function(shapes,sceneId) {
                    if(!this.isSceneCached(sceneId)) {
                        this.cache[sceneId] = [];
                        this.cache[sceneId].push(shapes);
                    }
                };
                this.isSceneCached = function(sceneId) {
                    return this.cache[sceneId]!=undefined;
                };
                this.getShapes = function(sceneId) {
                    return this.cache[sceneId][0];
                };
            };
            cacheForShapes = new cacheShapes();
            selScena.change(function() {
                selOdgovor.hide();
                $("#shapesLoading").show();
                selOdgovor.empty();
                sceneid.val(selScena.val());
                if(selScena.html()=="") {
                    selOdgovor.val("0");
                } else {
                    var trazenaScena = selScena.val();
                    /*if (cacheForShapes.isSceneCached(trazenaScena)) {
                        var response = cacheForShapes.getShapes(trazenaScena);
                        $.each(response, function (index, value) {
                            selOdgovor.append("<option value='" + value.id + "'>" + value.name + "</option>");
                        });
                        correctshape.val(selOdgovor.val());
                    } else { */
                        require(['core/ajax'], function (ajax) {
                            var promises = ajax.call([
                                {
                                    methodname: 'getPosAnsForScene',
                                    args: {sceneid: trazenaScena}
                                }
                            ]);

                            promises[0].done(function (response) {
                                $("#shapesLoading").hide();
                                selOdgovor.show();
                                cacheForShapes.add(response, trazenaScena);
                                $.each(response, function (index, value) {
                                    selOdgovor.append("<option value='" + value.id + "'>" + value.name + "</option>");
                                });
                                correctshape.val(selOdgovor.val());
                            }).fail(function (ex) {
                                console.log(ex);
                            });
                        });
                    //}
                }
            });
            selOdgovor.change(function() {
                correctshape.val(selOdgovor.val());
            });
        });
            // context menu za shape-ove
            var shapeContextMenu = function(shape,event) {
                if(event.button!=2) {
                    return false;
                }
                offset=$("canvas").first().offset();
                $(".hasmenu").trigger(
                    $.Event('contextmenu', {pageX: lastMouseX+offset.left, pageY: lastMouseY+offset.top, shape: shape})
                );
            };
            window.shapeContextMenu=shapeContextMenu;

            // funkcije za scene managment
            window.sceneDelById = function(o) {
                var sceneId = o.getAttribute('data-id');
                require(['core/ajax'], function (ajax) {
                    var promises = ajax.call([
                        {
                            methodname: 'qtypeManagment',
                            args: {type: 'delete-scene-by-id', id: sceneId}
                        }
                    ]);

                    promises[0].done(function (response) {
                        alert("Trazena scene je obrisana.");
                    }).fail(function (ex) {
                        console.log(ex);
                    });
                });
            };
        // Za pomeranje shape-ova
            var cellSize = 1.0;

            var lastMouseX = -1;
            var lastMouseY = -1;

            var draggedTransformNode = null;

//vectors in 3D world space, associated to mouse x/y movement on the screen
            var draggingUpVec    = null;
            var draggingRightVec = null;

            var unsnappedDragPos = null;


//------------------------------------------------------------------------------------------------------------------

            var mouseMoved = function(event)
            {
                //offsetX / offsetY polyfill for FF
                var target = event.target || event.srcElement;
                var rect = target.getBoundingClientRect();
                event.offsetX = event.clientX - rect.left;
                event.offsetY = event.clientY - rect.top;

                if (lastMouseX === -1)
                {
                    lastMouseX = event.offsetX;
                }
                if (lastMouseY === -1)
                {
                    lastMouseY = event.offsetY;
                }

                if (draggedTransformNode)
                {
                    dragObject(event.offsetX - lastMouseX, event.offsetY - lastMouseY);
                }

                lastMouseX = event.offsetX;
                lastMouseY = event.offsetY;
            };
            window.mouseMoved=mouseMoved;
//------------------------------------------------------------------------------------------------------------------

            var startDragging = function(transformNode,event)
            {
                // ako je desni klik ne radi nista
                if (event.button==2) {
                    return false;
                }
                //disable navigation during dragging
                document.getElementById("navInfo").setAttribute("type", '"NONE"');

                draggedTransformNode = transformNode;
                unsnappedDragPos     = new x3dom.fields.SFVec3f.parse(transformNode.getAttribute("translation"));


                //compute the dragging vectors in world coordinates
                //(since navigation is disabled, those will not change until dragging has been finished)

                //get the viewer's 3D local frame
                var x3dElem  = document.getElementById("someUniqueId");
                var vMatInv  = x3dElem.runtime.viewMatrix().inverse();
                var viewDir  = vMatInv.multMatrixVec(new x3dom.fields.SFVec3f(0.0, 0.0, -1.0));

                //use the viewer's up-vector and right-vector
                draggingUpVec    = vMatInv.multMatrixVec(new x3dom.fields.SFVec3f(0.0, 1.0,  0.0));;
                draggingRightVec = viewDir.cross(draggingUpVec);


                //project a world unit to the screen to get its size in pixels
                var p1 = x3dElem.runtime.calcCanvasPos(unsnappedDragPos.x, unsnappedDragPos.y, unsnappedDragPos.z);
                var p2 = x3dElem.runtime.calcCanvasPos(unsnappedDragPos.x + draggingRightVec.x,
                    unsnappedDragPos.y + draggingRightVec.y,
                    unsnappedDragPos.z + draggingRightVec.z)
                var magnificationFactor = 1.0 / Math.abs(p1[0] - p2[0]);

                //scale up vector and right vector accordingly
                draggingUpVec    = draggingUpVec.multiply(magnificationFactor);
                draggingRightVec = draggingRightVec.multiply(magnificationFactor);
            };
            window.startDragging=startDragging;
//------------------------------------------------------------------------------------------------------------------

            var dragObject = function(dx, dy)
            {
                //scale up vector and right vector accordingly
                var offsetUp    = draggingUpVec.multiply(-dy);
                var offsetRight = draggingRightVec.multiply(dx);

                unsnappedDragPos = unsnappedDragPos.add(offsetUp).add(offsetRight);

                var snappedDragPos;

                //if enabled, take grid snapping into account
                if (false)//document.getElementById("snapCheckbox").checked) // nema grid u nasem primeru
                {
                    snappedDragPos = new x3dom.fields.SFVec3f(cellSize * Math.ceil(unsnappedDragPos.x / cellSize),
                        cellSize * Math.ceil(unsnappedDragPos.y / cellSize),
                        cellSize * Math.ceil(unsnappedDragPos.z / cellSize));
                    draggedTransformNode.setAttribute("translation", snappedDragPos.toString());
                }
                else
                {
                    draggedTransformNode.setAttribute("translation", unsnappedDragPos.toString());
                }
            };
            window.dragObject=dragObject;
//------------------------------------------------------------------------------------------------------------------

            var stopDragging = function()
            {
                draggedTransformNode = null;
                draggingUpVec        = null;
                draggingRightVec     = null;
                unsnappedDragPos     = null;

                //re-enable navigation after dragging
                document.getElementById("navInfo").setAttribute("type", '"EXAMINE" "ANY"');
            };
            window.stopDragging=stopDragging;
        }
    };
    return x;
});