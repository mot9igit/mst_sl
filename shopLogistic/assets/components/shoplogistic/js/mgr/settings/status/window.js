shopLogistic.window.CreateStatus = function (config) {
    config = config || {};
    this.ident = config.ident || 'mecitem' + Ext.id();
    Ext.applyIf(config, {
        title: _('shoplogistic_menu_create'),
        width: 800,
        baseParams: {
            action: 'mgr/settings/status/create',
        },
    });
    shopLogistic.window.CreateStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStatus, shopLogistic.window.Default, {

    getFields: function (config) {
        return [
            {xtype: 'hidden', name: 'id', id: config.id + '-id'},
            {xtype: 'hidden', name: 'color', id: config.id + '-color'},
            {
                xtype: 'textfield',
                id: config.id + '-name',
                fieldLabel: _('shoplogistic_name'),
                name: 'name',
                anchor: '99%',
            },{
                xtype: 'shoplogistic-combo-ms2status',
                id: config.id + '-ms2status_id',
                fieldLabel: _('shoplogistic_ms2status_id'),
                name: 'ms2status_id',
                anchor: '99%',
            }, {
                xtype: 'colorpalette',
                fieldLabel: _('shoplogistic_color'),
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
            },{
                xtype: 'textarea',
                id: config.id + '-description',
                fieldLabel: _('shoplogistic_description'),
                name: 'description',
                anchor: '99%',
            },{
                xtype: 'checkboxgroup',
                hideLabel: true,
                columns: 2,
                items: [{
                    xtype: 'xcheckbox',
                    id: config.id + '-stores_available',
                    boxLabel: _('shoplogistic_status_stores_available'),
                    description: _('shoplogistic_status_stores_available_help'),
                    name: 'stores_available',
                    checked: parseInt(config.record['stores_available']),
                }, {
                    xtype: 'xcheckbox',
                    id: config.id + '-warehouses_available',
                    boxLabel: _('shoplogistic_status_warehouses_available'),
                    description: _('shoplogistic_status_warehouses_available_help'),
                    name: 'warehouses_available',
                    checked: parseInt(config.record['warehouses_available']),
                }]
            }, {
                xtype: 'checkboxgroup',
                hideLabel: true,
                columns: 3,
                items: [{
                    xtype: 'xcheckbox',
                    id: config.id + '-active',
                    boxLabel: _('shoplogistic_active'),
                    name: 'active',
                    checked: parseInt(config.record['active']),
                }, {
                    xtype: 'xcheckbox',
                    id: config.id + '-final',
                    boxLabel: _('shoplogistic_status_final'),
                    description: _('shoplogistic_status_final_help'),
                    name: 'final',
                    checked: parseInt(config.record['final']),
                }, {
                    xtype: 'xcheckbox',
                    id: config.id + '-fixed',
                    boxLabel: _('shoplogistic_status_fixed'),
                    description: _('shoplogistic_status_fixed_help'),
                    name: 'fixed',
                    checked: parseInt(config.record['fixed']),
                }]
            }
        ];
    },

    handleStatusFields: function (checkbox) {
        var type = checkbox.name.replace(/(^.*?_)/, '');

        var subject = Ext.getCmp(this.config.id + '-subject-' + type);
        var body = Ext.getCmp(this.config.id + '-body-' + type);
        if (checkbox.checked) {
            subject.enable().show();
            body.enable().show();
        } else {
            subject.hide().disable();
            body.hide().disable();
        }
    },

});
Ext.reg('shoplogistic-window-status-create', shopLogistic.window.CreateStatus);


shopLogistic.window.UpdateStatus = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_menu_update'),
        baseParams: {
            action: 'mgr/settings/status/update',
        },
    });
    shopLogistic.window.UpdateStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStatus, shopLogistic.window.CreateStatus);
Ext.reg('shoplogistic-window-status-update', shopLogistic.window.UpdateStatus);