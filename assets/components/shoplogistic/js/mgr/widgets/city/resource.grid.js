shopLogistic.grid.Resource = function (config) {
    config = config || {};
    
    config.id = 'shoplogistic-grid-resource';

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
            action: 'mgr/city/resource/getlist',
            resource: config.record.id
        },
        url: shopLogistic.config.connector_url,
        paging: true,
        pageSize: 20
    });
    shopLogistic.grid.Resource.superclass.constructor.call(this, config);
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
}
Ext.extend(shopLogistic.grid.Resource, MODx.grid.Grid);
Ext.reg('shoplogistic-grid-resource', shopLogistic.grid.Resource);


Ext.extend(shopLogistic.grid.Resource, MODx.grid.Grid, {
    getColumns: function () {
        return [
            {dataIndex: 'id', sortable: false, width: 70, header: 'ID', hidden: true},
            {dataIndex: 'city', sortable: false, width: 250, header: _('shoplogistic_city_grid_city')},
            {dataIndex: 'content', sortable: false, width: 220, header: _('shoplogistic_resource_grid_content')},
            {dataIndex: 'actions', width: 100, header: _('actions'), renderer: shopLogistic.utils.renderActions, sortable: false, id: 'actions'}
        ]
    },
    getFields: function () {
        return ['id','city','content','actions'];
    },
    getTbar: function (config) {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('add'),
            handler: function () {
                var w = MODx.load({ 
                    xtype: 'shoplogistic-window-resource',
                    id: Ext.id(),
                    listeners: {
                        success: {
                            fn: function () {
                                this.refresh();
                            }, scope: this
                        }
                    }
                });
                w.reset();
                w.setValues({resource: this.config.record.id});
                w.show();
            },
            scope: this
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
        
        console.log(this.menu.record);

        MODx.Ajax.request({
            url: shopLogistic.config.connector_url,
            params: {
                action: 'mgr/city/resource/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'shoplogistic-resource-window-update',
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
                action: 'mgr/city/resource/remove',
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
                this.getStore().baseParams[this.searchFields[i]] = Ext.getCmp(this.id + '-shoplogistic-resource-filter-' + this.searchFields[i]).getValue();
            }
        }
        this.getBottomToolbar().changePage(1);
    },

    filterClear: function () {
        if (this.searchFields.length > 0) {
            for (var i = 0; i < this.searchFields.length; i++) {
                Ext.getCmp(this.id + '-shoplogistic-resource-filter-' + this.searchFields[i]).reset();
            }
        }
        this.filterSend();
    },
}); 
Ext.reg('shoplogistic-grid-resource', shopLogistic.grid.Resource);