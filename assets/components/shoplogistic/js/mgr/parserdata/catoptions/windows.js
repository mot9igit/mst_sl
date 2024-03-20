shopLogistic.window.CreateOptionsParserdata = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_cats_options_create'),
        width: 600,
        baseParams: {
            action: 'mgr/parserdata/options/create',
        },
    });
    shopLogistic.window.CreateOptionsParserdata.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateOptionsParserdata, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parserdata_cats_options_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parserdata_cats_options_filter'),
            name: 'filter',
            id: config.id + '-filter',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parserdata_cats_options_to_field'),
            name: 'to_field',
            id: config.id + '-to_field',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-options',
            fieldLabel: _('shoplogistic_parserdata_cats_options_option_id'),
            name: 'option_id',
            hiddenName: "option_id",
            id: config.id + '-option_id',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parserdata_cats_options_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parserdata_cats_options_examples'),
            name: 'examples',
            id: config.id + '-examples',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-parserdata-options-create', shopLogistic.window.CreateOptionsParserdata);


shopLogistic.window.UpdateOptionsParserdata = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_cats_options_update'),
        baseParams: {
            action: 'mgr/parserdata/options/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateOptionsParserdata.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateOptionsParserdata, shopLogistic.window.CreateOptionsParserdata, {

    getFields: function (config) {
        return shopLogistic.window.CreateOptionsParserdata.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-parserdata-options-update', shopLogistic.window.UpdateOptionsParserdata);