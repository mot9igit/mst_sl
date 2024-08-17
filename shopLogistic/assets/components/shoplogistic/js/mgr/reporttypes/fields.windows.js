shopLogistic.window.CreateReportTypeField = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_reporttypefield_create'),
        width: 600,
        baseParams: {
            action: 'mgr/reportstypefields/create',
        },
    });
    shopLogistic.window.CreateReportTypeField.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateReportTypeField, shopLogistic.window.Default, {
    getFields: function (config) {
        return shopLogistic.window.CreateReportTypeField.prototype.getFormFields.call(this, config)
    },
    getFormFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'type',
            id: config.id + '-type',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_reporttypefield_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        },{
            xtype: 'combo-field_type',
            fieldLabel: _('shoplogistic_reporttypefield_field_type'),
            name: 'field_type',
            hiddenName: 'field_type',
            anchor: '99%',
            id: config.id + '-field_type',
            allowBlank: false
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_reporttypefield_key'),
            name: 'key',
            id: config.id + '-key',
            anchor: '99%'
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_reporttypefield_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            id: config.id + '-toplan',
            boxLabel: _('shoplogistic_reporttypefield_toplan'),
            name: 'toplan',
            checked: false,
        },{
            xtype: 'xcheckbox',
            id: config.id + '-active',
            boxLabel: _('shoplogistic_reporttypefield_active'),
            name: 'active',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-report-type-field-create', shopLogistic.window.CreateReportTypeField);


shopLogistic.window.UpdateReportTypeField = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_reporttypefield_update'),
        baseParams: {
            action: 'mgr/reportstypefields/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateReportTypeField.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateReportTypeField, shopLogistic.window.CreateReportTypeField, {

    getFields: function (config) {
        return shopLogistic.window.CreateReportTypeField.prototype.getFormFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-report-type-field-update', shopLogistic.window.UpdateReportTypeField);