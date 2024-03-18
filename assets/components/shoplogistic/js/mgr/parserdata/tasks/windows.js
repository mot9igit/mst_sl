shopLogistic.window.CreateParserdataTask = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_tasks_create'),
        width: 900,
        baseParams: {
            action: 'mgr/parserdata/tasks/create'
        },
    });
    shopLogistic.window.CreateParserdataTask.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateParserdataTask, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parserdata_tasks_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parserdata_tasks_url'),
            name: 'url',
            id: config.id + '-url',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-parserdata-task-status',
            fieldLabel: _('shoplogistic_parserdata_tasks_status'),
            name: 'status',
            hiddenName: "status",
            id: config.id + '-status',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_parserdata_tasks_external_id'),
            name: 'external_id',
            id: config.id + '-external_id',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parserdata_tasks_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }];
    }
});
Ext.reg('shoplogistic-window-parserdata-tasks-create', shopLogistic.window.CreateParserdataTask);


shopLogistic.window.UpdateParserdataTask = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parserdata_config_fields_update'),
        width: 900,
        maxHeight: 400,
        baseParams: {
            action: 'mgr/parserdata/tasks/update'
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateParserdataTask.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateParserdataTask, shopLogistic.window.CreateParserdataTask, {

    getFields: function (config) {
        return shopLogistic.window.CreateParserdataTask.prototype.getFields.call(this, config)
    }

});
Ext.reg('shoplogistic-window-parserdata-tasks-update', shopLogistic.window.UpdateParserdataTask);