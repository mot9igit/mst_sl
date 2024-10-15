shopLogistic.grid.StoreRemainsCats = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-storeremains-cats';
    }
    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains/cats/getlist',
            sort: 'id',
            dir: 'asc',
            store_id: config.record.object.id
        },
        multi_select: true,
        stateful: true,
        stateId: config.record.object.id,
        topbar: 1
    });
    shopLogistic.grid.StoreRemainsCats.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.StoreRemainsCats, shopLogistic.grid.Default, {
    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStoreCat(grid, e, row);
            },
        };
    },
    createStoreCat: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-storeremains-cats-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-storeremains-cats-create',
            id: 'shoplogistic-window-storeremains-cats-create',
            record: this.menu.record,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues({
            store_id: config.record.object.id
        });
        w.show(e.target);
    },

    updateStoreCat: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-storeremains-cats-update');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-storeremains-cats-update',
            id: 'shoplogistic-window-storeremains-cats-update',
            record: this.menu.record,
            title: this.menu.record['name'],
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues(this.menu.record);
        w.show(e.target);
    },

    removeStoreCat: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_remove')
                : _('shoplogistic_remove'),
            text: ids.length > 1
                ? _('shoplogistic_confirm_remove')
                : _('shoplogistic_confirm_remove'),
            url: this.config.url,
            params: {
                action: 'mgr/storeremains/cats/remove',
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

    getFields: function () {
        return ['id', 'store_id', 'guid', 'parent_guid', 'base_guid', 'name', 'name_alt', 'description', 'published', 'active', 'createdon', 'updatedon', 'actions'];
    },

    getColumns: function () {
        return [{
            header: "GUID",
            dataIndex: 'guid',
            sortable: true,
            width: 200
        },{
            header: "GUID родителя",
            dataIndex: 'parent_guid',
            sortable: true,
            width: 70,
        },{
            header: "GUID БД",
            dataIndex: 'base_guid',
            sortable: true,
            width: 70,
        },{
            header: "Наименование СИ",
            dataIndex: 'name',
            sortable: true,
            width: 70,
        },{
            header: "Наименование",
            dataIndex: 'name_alt',
            sortable: true,
            width: 70,
        },{
            header: "Опубликован",
            dataIndex: "published",
            renderer: shopLogistic.utils.renderBoolean,
            sortable: true,
            width: 100
        },{
            header: "Активен",
            dataIndex: "active",
            renderer: shopLogistic.utils.renderBoolean,
            sortable: true,
            width: 100
        }, {
            header: _('shoplogistic_grid_actions'),
            dataIndex: 'actions',
            renderer: shopLogistic.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    getTopBar: function (config) {
        if(config.topbar){
            return [{
                text: '<i class="icon icon-plus"></i>&nbsp;Создать каталог',
                handler: this.createStoreCat,
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
        }else{
            return ['->', {
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
        }
    }
});
Ext.reg('shoplogistic-grid-storeremains-cats', shopLogistic.grid.StoreRemainsCats);
