shopLogistic.grid.Association = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-association';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/settings/association/getlist',
            sort: 'id',
            dir: 'desc',
        },
        stateful: true,
        stateId: config.id,
        multi_select: true,
    });
    shopLogistic.grid.Association.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Association, shopLogistic.grid.Default, {

    getFields: function () {
        return [
            'id', 'association', 'description', 'brand', 'brand_id', 'properties', 'actions'
        ];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_id'),
                dataIndex: 'id',
                width: 30
            },
            {
                header: _('shoplogistic_brand_association_association'),
                dataIndex: 'association',
                width: 50
            },
            {
                header: _('shoplogistic_brand_association_brand'),
                dataIndex: 'brand',
                width: 50
            },
            {
                header: _('shoplogistic_actions'),
                dataIndex: 'actions',
                id: 'actions',
                width: 50,
                renderer: shopLogistic.utils.renderActions
            }
        ];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_btn_create'),
            handler: this.createAssociation,
            scope: this
        }, '->', this.getSearchField()];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateAssociation(grid, e, row);
            },
        };
    },

    createAssociation: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-association-create');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-association-create',
            id: 'shoplogistic-window-association-create',
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.show(e.target);
    },

    updateAssociation: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-association-update');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-association-update',
            id: 'shoplogistic-window-association-update',
            title: this.menu.record['association'],
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
        w.fp.getForm().setValues(this.menu.record);
        w.show(e.target);
    },

    removeAssociation: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_brand_associations_remove')
                : _('shoplogistic_brand_association_remove'),
            text: ids.length > 1
                ? _('shoplogistic_brand_associations_remove_confirm')
                : _('shoplogistic_brand_association_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/settings/association/remove',
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
    }
});
Ext.reg('shoplogistic-grid-association', shopLogistic.grid.Association);