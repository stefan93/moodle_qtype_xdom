/**
 * Created by Stefan on 16.5.2016.
 */
define([ 'qtype_xdom/sceneModel','qtype_xdom/shapeModel','jquery','jqueryui','jquery-ui/menu','x3dom' ], function(Scene,Shape,$) {
    var x = {
        init: function() {
            var $tabs= $('#tabs'),
             $tabsLoading= $('#tabsLoading');
            this.$tabs.tabs({
                create: function() {
                    $tabs.show();
                    $tabsLoading.hide();
                    require([ 'qtypq_xdom/questionSettings' ], function(qs) {
                        qs.init();
                    });
                },
                beforeActivate: function(event, ui) {
                        switch(ui.newPanel.selector) {
                            case '#fragment-2':
                                require(['qtype_xdom/currentSceneSettings'], function(cSS) {
                                    cSS.init();
                                });
                                break;
                            case '#fragment-1':
                                require(['qtype_xdom/questionSettings'], function(qs) {
                                    qs.reload();
                                });
                                break;
                            case '#fragment-3':
                                require(['qtype_xdom/sceneManagment'], function(sm) {
                                    sm.init();
                                });
                                break;
                            default :
                                break;
                        }
                }
            });
        },
        boundEvents: function() {

        }
    };
    return x;
});