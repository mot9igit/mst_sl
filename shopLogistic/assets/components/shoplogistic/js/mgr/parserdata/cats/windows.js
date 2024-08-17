shopLogistic.window.CreateCatsParserdata = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_cats_create'),
        width: 900,
        baseParams: {
            action: 'mgr/parserdata/categories/create'
        },
    });
    shopLogistic.window.CreateCatsParserdata.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateCatsParserdata, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'hidden',
            name: 'service_id',
            id: config.id + '-service_id',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_parserdata_cats_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_parserdata_cats_export_parents'),
            name: 'export_parents',
            id: config.id + '-export_parents',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_parserdata_cats_check'),
            name: 'check',
            id: config.id + '-check',
            checked: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parserdata_cats_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'shoplogistic-combo-category',
            boxLabel: _('shoplogistic_parserdata_cats_cat_id'),
            name: 'cat_id',
            hiddenName: 'cat_id',
            id: config.id + '-cat_id'
        }];
    },
});
Ext.reg('shoplogistic-window-parserdata-cats-create', shopLogistic.window.CreateCatsParserdata);


shopLogistic.window.UpdateCatsParserdata = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_cats_update'),
        width: 900,
        maxHeight: 400,
        baseParams: {
            action: 'mgr/parserdata/categories/update'
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateCatsParserdata.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateCatsParserdata, shopLogistic.window.CreateCatsParserdata, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            autoHeight: true,
            deferredRender: false,
            forceLayout: true,
            width: '98%',
            items: [{
                title: _('shoplogistic_parserdata_cats_update'),
                layout: 'form',
                items: shopLogistic.window.CreateCatsParserdata.prototype.getFields.call(this, config)
            }, {
                title: _('shoplogistic_parserdata_cats_options'),
                items: [{
                    html: 'Будьте внимательны при заполнений соответствий. Если не отмечена галочка "Игнорировать" и не стоит соответствие, при импорте опция будет создана.',
                    cls: 'panel-desc'
                },{
                    xtype: 'shoplogistic-grid-parserdata-cats-options',
                    record: config.record,
                }]
            }],
            listeners: {
                'tabchange': {fn: function(panel) {
                        panel.doLayout();
                    },
                    scope: this
                }
            }
        }]
    }

});
Ext.reg('shoplogistic-window-parserdata-cats-update', shopLogistic.window.UpdateCatsParserdata);