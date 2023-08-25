shopLogistic.window.CreateStatusDoc = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_doc_status_create'),
        width: 600,
        baseParams: {
            action: 'mgr/docsstatus/create',
        },
    });
    shopLogistic.window.CreateStatusDoc.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStatusDoc, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'color',
            id: config.id + '-color'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_doc_status_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_doc_status_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_doc_status_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_doc_status_has_action'),
            name: 'has_action',
            id: config.id + '-has_action',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_doc_status_has_submit'),
            name: 'has_submit',
            id: config.id + '-has_submit',
            checked: false,
        }, {
            xtype: 'colorpalette',
            fieldLabel: _('shoplogistic_doc_status_color'),
            id: config.id + '-color-palette',
            listeners: {
                select: function (palette, color) {
                    Ext.getCmp(config.id + '-color').setValue(color)
                },
                beforerender: function (palette) {
                    if (config.record['color'] != undefined) {
                        palette.value = config.record['color'];
                    }
                }
            },
        }];
    },
});
Ext.reg('shoplogistic-window-files-docs-statuses-create', shopLogistic.window.CreateStatusDoc);


shopLogistic.window.UpdateStatusDoc = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/docsstatus/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStatusDoc.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStatusDoc, shopLogistic.window.CreateStatusDoc, {

    getFields: function (config) {
        return shopLogistic.window.CreateStatusDoc.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-files-docs-statuses-update', shopLogistic.window.UpdateStatusDoc);