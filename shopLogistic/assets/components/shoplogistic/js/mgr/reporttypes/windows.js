shopLogistic.window.CreateReportType = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_reporttype_create'),
        width: 600,
        baseParams: {
            action: 'mgr/reportstype/create',
        },
    });
    shopLogistic.window.CreateReportType.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateReportType, shopLogistic.window.Default, {
    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_reporttype_create'),
                layout: 'form',
                items: shopLogistic.window.CreateReportType.prototype.getFormFields.call(this, config),
            }]
        }]
    },
    getFormFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_reporttype_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_stores_docs_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            id: config.id + '-toplan',
            boxLabel: _('shoplogistic_reporttype_toplan'),
            name: 'toplan',
            checked: false,
        },{
            xtype: 'xcheckbox',
            id: config.id + '-active',
            boxLabel: _('shoplogistic_reporttype_active'),
            name: 'active',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-report-type-create', shopLogistic.window.CreateReportType);


shopLogistic.window.UpdateReportType = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_reporttype_update'),
        baseParams: {
            action: 'mgr/reportstype/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateReportType.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateReportType, shopLogistic.window.CreateReportType, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_reporttype_update'),
                layout: 'form',
                items: shopLogistic.window.CreateReportType.prototype.getFormFields.call(this, config),
            }, {
                title: _('shoplogistic_reporttypefields'),
                items: [{
                    xtype: 'shoplogistic-grid-reporttypefields',
                    record: config.record,
                }]
            }]
        }];
    }

});
Ext.reg('shoplogistic-window-report-type-update', shopLogistic.window.UpdateReportType);