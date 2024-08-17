shopLogistic.window.CreateTask = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parser_tasks_create'),
        width: 900,
        baseParams: {
            action: 'mgr/parser/tasks/create'
        },
    });
    shopLogistic.window.CreateTask.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateTask, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_tasks_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_tasks_url'),
            name: 'url',
            id: config.id + '-url',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-parser-task-status',
            fieldLabel: _('shoplogistic_parser_tasks_status'),
            name: 'status',
            hiddenName: "status",
            id: config.id + '-status',
            anchor: '99%'
        },{
            xtype: 'shoplogistic-combo-parser-config',
            fieldLabel: _('shoplogistic_parser_tasks_config_id'),
            name: 'config_id',
            hiddenName: "config_id",
            id: config.id + '-config_id',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_tasks_file'),
            name: 'file',
            id: config.id + '-file',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parser_tasks_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parser_tasks_exclude'),
            name: 'exclude',
            id: config.id + '-exclude',
            anchor: '99%'
        }];
    }
});
Ext.reg('shoplogistic-window-parser-tasks-create', shopLogistic.window.CreateTask);


shopLogistic.window.UpdateTask = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parser_config_fields_update'),
        width: 900,
        maxHeight: 400,
        baseParams: {
            action: 'mgr/parser/tasks/update'
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateTask.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateTask, shopLogistic.window.CreateTask, {

    getFields: function (config) {
        return shopLogistic.window.CreateTask.prototype.getFields.call(this, config)
    }

});
Ext.reg('shoplogistic-window-parser-tasks-update', shopLogistic.window.UpdateTask);