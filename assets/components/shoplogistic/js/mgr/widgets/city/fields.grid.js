shopLogistic.grid.Fields = function (config) {
    config = config || {};
    
    config.id = 'shoplogistic-grid-fields';

    Ext.apply(config, {
        columns: this.getColumns(),
        fields: this.getFields(),
        tbar: this.getTbar(config),
        autoHeight: true,
        sm: new Ext.grid.CheckboxSelectionModel(),
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {}
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateItem(grid, e, row);
            }
        },
        baseParams: {
            action: 'mgr/city/fields/getlist',
            city: config.record.id
        },
        url: shopLogistic.config.connector_url,
        paging: true,
        pageSize: 20
    });
    shopLogistic.grid.Fields.superclass.constructor.call(this, config);
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
}
Ext.extend(shopLogistic.grid.Fields, MODx.grid.Grid);
Ext.reg('shoplogistic-grid-fields', shopLogistic.grid.Fields);


Ext.extend(shopLogistic.grid.Fields, MODx.grid.Grid, {
    getColumns: function () {
        return [
            {dataIndex: 'id', sortable: true, width: 70, header: 'ID'},
            {dataIndex: 'name', sortable: true, width: 250, header: _('shoplogistic_fields_grid_name')},
            {dataIndex: 'key', sortable: true, width: 220, header: _('shoplogistic_fields_grid_key')},
            {dataIndex: 'value', sortable: true, width: 220, header: _('shoplogistic_fields_grid_value')},
            {dataIndex: 'actions', width: 100, header: _('actions'), renderer: shopLogistic.utils.renderActions, sortable: false, id: 'actions'}
        ]
    },
    getFields: function () {
        return ['id','name','key','value','actions'];
    },
    getTbar: function (config) {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('add'),
            handler: function () {
                var w = MODx.load({ 
                    xtype: 'shoplogistic-window-fields',
                    listeners: {
                        success: {
                            fn: function () {
                                this.refresh();
                            }, scope: this
                        }
                    }
                });
                
                w.setValues({city: this.config.record.id});
                w.show();
            },
            scope: this
        }, '->',  {
            xtype: 'textfield',
            id: config.id + '-shoplogistic-fields-filter-query',
            emptyText: _('shoplogistic_fields_grid_search_empty'),
            width: 250,
            listeners: {
                render: {
                    fn: function (field) {
                        field.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
                            this.filterSend();
                        }, this);
                    }, scope: this
                },
            }
        }, {
            xtype: 'button',
            text: '<i class="icon icon-check"></i>',
            handler: this.filterSend,
        }, {
            xtype: 'button',
            text: '<i class="icon icon-times"></i>',
            handler: this.filterClear,
        }];
    },
    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = shopLogistic.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },
    updateItem: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: shopLogistic.config.connector_url,
            params: {
                action: 'mgr/city/fields/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'shoplogistic-fields-window-update',
                            id: Ext.id(),
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },
    removeItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: _('confirm'),
            text: _('remove'),
            url: this.config.url,
            params: {
                action: 'mgr/city/fields/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    },
    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof(row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                }
                else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },
    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();

        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }

        return ids;
    },
    
    searchFields: ['query'],

    filterSend: function () {
        if (this.searchFields.length > 0) {
            for (var i = 0; i < this.searchFields.length; i++) {
                this.getStore().baseParams[this.searchFields[i]] = Ext.getCmp(this.id + '-shoplogistic-fields-filter-' + this.searchFields[i]).getValue();
            }
        }
        this.getBottomToolbar().changePage(1);
    },

    filterClear: function () {
        if (this.searchFields.length > 0) {
            for (var i = 0; i < this.searchFields.length; i++) {
                Ext.getCmp(this.id + '-shoplogistic-fields-filter-' + this.searchFields[i]).reset();
            }
        }
        this.filterSend();
    },
}); 
Ext.reg('shoplogistic-grid-fields', shopLogistic.grid.Fields);