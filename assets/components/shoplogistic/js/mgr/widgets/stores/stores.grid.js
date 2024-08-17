shopLogistic.grid.Stores = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-stores';
    }
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/store/getlist',
            //type: config.type
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStore(grid, e, row);
            }
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active
                    ? 'shoplogistic-grid-row-disabled'
                    : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });

    shopLogistic.grid.Stores.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.Stores, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = shopLogistic.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createStore: function (btn, e) {
        var w = MODx.load({
            xtype: 'shoplogistic-store-window-create',
            id: Ext.id(),
            type: this.config.baseParams.type,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({active: true});
        if(this.config.baseParams.type == 1){
            w.setValues({store: true});
        }
        if(this.config.baseParams.type == 2){
            w.setValues({warehouse: true});
        }
        if(this.config.baseParams.type == 3){
            w.setValues({vendor: true});
        }

        w.show(e.target);
    },

    updateStore: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/store/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'shoplogistic-store-window-update',
                            id: Ext.id(),
                            record: r,
                            type: r.object.type,
                            store: r.object.store,
                            warehouse: r.object.warehouse,
                            vendor: r.object.vendor,
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

    removeStore: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_stores_remove')
                : _('shoplogistic_store_remove'),
            text: ids.length > 1
                ? _('shoplogistic_stores_remove_confirm')
                : _('shoplogistic_store_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/store/remove',
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

    disableStore: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/store/disable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    enableStore: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/store/enable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    getFields: function () {
        return [
            'id',
            'org_id',
            'type_integration',
            'org',
            'type',
            'name',
            'apikey',
            'company_type',
            'ur_name',
            'inn',
            'bank_number',
            'bank_knumber',
            'bank_bik',
            'bank_name',
            'unique_id',
            'btx24_id',
            'address' ,
            'ur_address',
            'city',
            'description',
            'store',
            'warehouse',
            'vendor',
            'store',
            'marketplace',
            'opt_marketplace',
            'active',
            'check_remains',
            'check_docs',
            'actions'
        ];
    },

    getColumns: function (config) {
        var fields = [
            'id',
            'name',
            'apikey',
            'city',
            'btx24_id',
            'description'
        ];
        var checks = [
            'active',
            'check_remains',
            'check_docs',
            'marketplace',
            'opt_marketplace'
        ];
        console.log(config.type)
        if(config.type === 1){
            // fields.push('elem1', 'elem2')
        }
        if(config.type === 2){
            // fields.push('elem1', 'elem2')
            checks.push('delivery_tk');
        }
        if(config.type === 3){

        }
        var ff = [];
        fields.forEach(element => {
            ff.push({
                header: _('shoplogistic_store_' + element),
                dataIndex: element,
                sortable: true,
                width: 70
            });
        });
        checks.forEach(element => {
            ff.push({
                header: _('shoplogistic_store_' + element),
                dataIndex: element,
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100
            });
        });
        ff.push({
            header: _('shoplogistic_grid_actions'),
            dataIndex: 'actions',
            renderer: shopLogistic.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        });
        return ff;
    },

    getTopBar: function (config) {
        if(config.type == 1){
            var anchor = _('shoplogistic_store_create');
        }
        if(config.type == 2){
            var anchor = _('shoplogistic_warehouse_create');
        }
        if(config.type == 3){
            var anchor = _('shoplogistic_vendor_create');
        }
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + anchor,
            handler: this.createStore,
            scope: this
        }, '->', {
            xtype: 'shoplogistic-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
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

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('shoplogistic-grid-stores', shopLogistic.grid.Stores);
