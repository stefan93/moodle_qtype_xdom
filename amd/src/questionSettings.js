/**
 * Created by Stefan on 16.5.2016.
 */
define(['jquery'],function($) {
    return x={
        $shapesLoading: $("#shapesLoading"),
        $sceneid: $("[name=sceneid]"),
        $correctshape: $("[name=correctshape]"),
        $selScena: $('#izabranascena'),
        $selOdgovor: $('#izabranodgovor'),
        init: function() {
            x.bindEvents();
        },
        reload: function() {
            x.startLoadingShapes();
            x.addShapesInList(x.$selOdgovor.val());
            x.stopLoadingShapes();
        },
        bindEvents: function() {
            x.$selScena.change(x.reload());
            x.$selOdgovor.change(function() {
                x.$correctshape.val(x.$selOdgovor);
            });
        },
        addShapesInList: function(sceneid) {
            require(['core/ajax'], function (ajax) {
                var promises = ajax.call([
                    {
                        methodname: 'getPosAnsForScene',
                        args: {sceneid: sceneid}
                    }
                ]);

                promises[0].done(function (response) {
                    x.$selOdgovor.empty();
                    $.each(response, function (index, value) {
                        x.$selOdgovor.append("<option value='" + value.id + "'>" + value.name + "</option>");
                    });
                }).fail(function (ex) {
                    console.error(ex);
                });
            });
        },
        startLoadingShapes: function() {
            x.$shapesLoading.show();
            x.$selOdgovor.hide();
        },
        stopLoadingShapes: function() {
            x.$shapesLoading.hide();
            x.$selOdgovor.show();
        }
    }
});