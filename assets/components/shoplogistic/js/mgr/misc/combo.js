shopLogistic.combo.ComboBoxDefault = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        assertValue : function () {
            var val = this.getRawValue(),
                rec;
            if (this.valueField && Ext.isDefined(this.value)) {
                rec = this.findRecord(this.valueField, this.value);
            }
            /* fix for https://github.com/bezumkin/miniShop2/pull/350
            if(!rec || rec.get(this.displayField) != val){
                rec = this.findRecord(this.displayField, val);
            }*/
            if (rec && rec.get(this.displayField) != val) {
                rec = null;
            }
            if (!rec && this.forceSelection) {
                if (val.length > 0 && val != this.emptyText) {
                    this.el.dom.value = Ext.value(this.lastSelectionText, '');
                    this.applyEmptyText();
                } else {
                    this.clearValue();
                }
            } else {
                if (rec && this.valueField) {
                    if (this.value == val) {
                        return;
                    }
                    val = rec.get(this.valueField || this.displayField);
                }
                this.setValue(val);
            }
        },

    });
    shopLogistic.combo.ComboBoxDefault.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.ComboBoxDefault, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-combobox-default', shopLogistic.combo.ComboBoxDefault);

shopLogistic.combo.User = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'user',
        fieldLabel: config.name || 'createdby',
        hiddenName: config.name || 'createdby',
        displayField: 'fullname',
        valueField: 'id',
        anchor: '99%',
        fields: ['username', 'id', 'fullname'],
        pageSize: 20,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        url: shopLogistic.config['connector_url'],
        baseParams: {
            action: 'mgr/system/user/getlist',
            combo: true,
        },
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{username}</b>\
                        <tpl if="fullname && fullname != username"> - {fullname}</tpl>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.User.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.User, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-user', shopLogistic.combo.User);

shopLogistic.combo.Stage = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'stage',
        fieldLabel: config.name || 'stage',
        hiddenName: config.name || 'stage',
        displayField: 'name',
        valueField: 'id',
        anchor: '99%',
        fields: ['name', 'id', 'category'],
        pageSize: 20,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        url: shopLogistic.config['connector_url'],
        baseParams: {
            action: 'mgr/system/stage/getlist',
            combo: true,
        },
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                        - {category}\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Stage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Stage, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-stage', shopLogistic.combo.Stage);

shopLogistic.combo.Classes = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'shoplogistic-combo-classes',
        fieldLabel: _('shoplogistic_class'),
        name: 'class',
        hiddenName: 'class',
        displayField: 'class',
        valueField: 'class',
        pageSize: 20,
        fields: ['type', 'class'],
        url: miniShop2.config['connector_url'],
        baseParams: {
            action: 'mgr/settings/getclass',
            type: config.type || '',
        },
        allowBlank: true,
        editable: true,
    });
    shopLogistic.combo.Classes.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Classes, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-classes', shopLogistic.combo.Classes);

shopLogistic.combo.Store = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'store_id',
        fieldLabel: config.name || 'store_id',
        hiddenName: config.name || 'store_id',
        displayField: 'name',
        valueField: 'id',
        anchor: '99%',
        fields: ['id', 'name'],
        pageSize: 50,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        url: shopLogistic.config['connector_url'],
        baseParams: {
            action: 'mgr/store/getlist',
            combo: true,
        },
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Store.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Store, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-store', shopLogistic.combo.Store);

shopLogistic.combo.Warehouse = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'warehouse_id',
        fieldLabel: config.name || 'warehouse_id',
        hiddenName: config.name || 'warehouse_id',
        displayField: 'name',
        valueField: 'id',
        anchor: '99%',
        fields: ['id', 'name'],
        pageSize: 20,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        url: shopLogistic.config['connector_url'],
        baseParams: {
            action: 'mgr/warehouse/getlist',
            combo: true,
        },
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Warehouse.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Warehouse, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-warehouse', shopLogistic.combo.Warehouse);

shopLogistic.combo.Registry = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'registry_id',
        fieldLabel: config.name || 'registry_id',
        hiddenName: config.name || 'registry_id',
        displayField: 'name',
        valueField: 'id',
        anchor: '99%',
        fields: ['id', 'num', 'date_from', 'date_to'],
        pageSize: 20,
        typeAhead: false,
        editable: true,
        allowBlank: true,
        url: shopLogistic.config['connector_url'],
        baseParams: {
            action: 'mgr/registry/getlist',
            //store_id: config.record.object.store_id,
            combo: true,
        },
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{num} ({date_from} - {date_to})</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Registry.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Registry, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-registry', shopLogistic.combo.Registry);

shopLogistic.combo.Search = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        xtype: 'twintrigger',
        ctCls: 'x-field-search',
        allowBlank: true,
        msgTarget: 'under',
        emptyText: _('search'),
        name: 'query',
        triggerAction: 'all',
        clearBtnCls: 'x-field-search-clear',
        searchBtnCls: 'x-field-search-go',
        onTrigger1Click: this._triggerSearch,
        onTrigger2Click: this._triggerClear,
    });
    shopLogistic.combo.Search.superclass.constructor.call(this, config);
    this.on('render', function () {
        this.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
            this._triggerSearch();
        }, this);
    });
    this.addEvents('clear', 'search');
};
Ext.extend(shopLogistic.combo.Search, Ext.form.TwinTriggerField, {

    initComponent: function () {
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);
        this.triggerConfig = {
            tag: 'span',
            cls: 'x-field-search-btns',
            cn: [
                {tag: 'div', cls: 'x-form-trigger ' + this.searchBtnCls},
                {tag: 'div', cls: 'x-form-trigger ' + this.clearBtnCls}
            ]
        };
    },

    _triggerSearch: function () {
        this.fireEvent('search', this);
    },

    _triggerClear: function () {
        this.fireEvent('clear', this);
    },

});
Ext.reg('shoplogistic-combo-search', shopLogistic.combo.Search);
Ext.reg('shoplogistic-field-search', shopLogistic.combo.Search);

shopLogistic.combo.Vendor = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        name: config.name || 'vendor',
        fieldLabel: _('shoplogistic_' + config.name || 'vendor'),
        hiddenName: config.name || 'vendor',
        displayField: 'name',
        valueField: 'id',
        anchor: '99%',
        fields: ['name', 'id'],
        pageSize: 20,
        url: shopLogistic.config['connector_url'],
        typeAhead: true,
        editable: true,
        allowBlank: true,
        emptyText: _('no'),
        minChars: 1,
        forceSelection: false,
        baseParams: {
            action: 'mgr/system/vendor/getlist',
            combo: true,
            id: config.value,
        }
    });
    shopLogistic.combo.Vendor.superclass.constructor.call(this, config);
    this.on('expand', function () {
        if (!!this.pageTb) {
            this.pageTb.show();
        }
    });
};
Ext.extend(shopLogistic.combo.Vendor, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-vendor', shopLogistic.combo.Vendor);

shopLogistic.combo.store_type = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['type', 'display']
            ,data: [
                ['1', 'Магазин']
                ,['2', 'Оптовая компания']
                ,['3', 'Производитель']
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'type'
    });
    shopLogistic.combo.store_type.superclass.constructor.call(this,config);
};
Ext.extend(shopLogistic.combo.store_type, MODx.combo.ComboBox);
Ext.reg('combo-store_type', shopLogistic.combo.store_type);

shopLogistic.combo.company_type = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['company_type','display']
            ,data: [
                ['ИП','ИП']
                ,['ООО','ООО']
                ,['ОАО','ОАО']
                ,['ЗАО','ЗАО']
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'company_type'
    });
    shopLogistic.combo.company_type.superclass.constructor.call(this,config);
};
Ext.extend(shopLogistic.combo.company_type,MODx.combo.ComboBox);
Ext.reg('combo-company_type',shopLogistic.combo.company_type);

shopLogistic.combo.balance_type = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['type', 'display']
            ,data: [
                ['1', 'Начисление']
                ,['2', 'Списание']
                ,['3', 'Информационное']
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'type'
    });
    shopLogistic.combo.balance_type.superclass.constructor.call(this,config);
};
Ext.extend(shopLogistic.combo.balance_type, MODx.combo.ComboBox);
Ext.reg('combo-balance_type', shopLogistic.combo.balance_type);

shopLogistic.combo.field_type = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['type', 'display']
            ,data: [
                [1, 'Поле отчета']
                ,[2, 'Параметр']
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'type'
    });
    shopLogistic.combo.field_type.superclass.constructor.call(this,config);
};
Ext.extend(shopLogistic.combo.field_type, MODx.combo.ComboBox);
Ext.reg('combo-field_type', shopLogistic.combo.field_type);

shopLogistic.combo.City = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/city/load/city',
        },
        name: 'city',
        hiddenName: 'city',
        fields: ['id', 'city'],
        mode: 'remote',
        displayField: 'city',
        fieldLabel: _('shoplogistic_city_grid_city'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: false
    });
    shopLogistic.combo.City.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.City, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-city', shopLogistic.combo.City);

shopLogistic.combo.Product = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/product/getlist',
        },
        name: 'product_id',
        hiddenName: 'product_id',
        fields: ['id', 'pagetitle', 'article', 'price'],
        mode: 'remote',
        displayField: 'pagetitle',
        fieldLabel: _('shoplogistic_storeremains_product_name'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{pagetitle} ({article}) {price} р.</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Product.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Product, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-product', shopLogistic.combo.Product);

shopLogistic.combo.Category = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/category/getlist',
        },
        name: 'category_id',
        hiddenName: 'category_id',
        fields: ['id', 'pagetitle'],
        mode: 'remote',
        displayField: 'pagetitle',
        fieldLabel: _('shoplogistic_category'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{pagetitle}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Category.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Category, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-category', shopLogistic.combo.Category);

shopLogistic.combo.Options = function (config) {
    config = config || {};
    // console.log(config);
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/options/getlist',
        },
        name: 'option_id',
        hiddenName: 'option_id',
        fields: ['id', 'caption'],
        mode: 'remote',
        displayField: 'caption',
        fieldLabel: _('shoplogistic_options'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{caption}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Options.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Options, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-options', shopLogistic.combo.Options);

shopLogistic.combo.ms2Status = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/ms2status/getlist',
        },
        name: 'ms2status_id',
        hiddenName: 'ms2status_id',
        fields: ['id', 'name', 'description'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_ms2status_id'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.ms2Status.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.ms2Status, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-ms2status', shopLogistic.combo.ms2Status);

shopLogistic.combo.docStatus = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/doc_status/getlist',
        },
        name: 'status',
        hiddenName: 'status',
        fields: ['id', 'name', 'description'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_doc_status_id'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.docStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.docStatus, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-docstatus', shopLogistic.combo.docStatus);

shopLogistic.combo.exportFileStatus = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/export_files_status/getlist',
        },
        name: 'status',
        hiddenName: 'status',
        fields: ['id', 'name', 'description'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_doc_status_id'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.exportFileStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.exportFileStatus, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-exportfilestatus', shopLogistic.combo.exportFileStatus);

shopLogistic.combo.cardRequestStatus = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/card_request_status/getlist',
        },
        name: 'status',
        hiddenName: 'status',
        fields: ['id', 'name', 'description'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_card_request_id'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.cardRequestStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.cardRequestStatus, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-cardrequeststatus', shopLogistic.combo.cardRequestStatus);

shopLogistic.combo.balancePayRequestStatus = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/balance_pay_request_status/getlist',
        },
        name: 'status',
        hiddenName: 'status',
        fields: ['id', 'name', 'description'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_balance_pay_request_id'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.balancePayRequestStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.balancePayRequestStatus, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-balancepayrequeststatus', shopLogistic.combo.balancePayRequestStatus);

shopLogistic.combo.productField = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/order_field/getproduct',
        },
        name: 'field',
        hiddenName: 'field',
        fields: ['val', 'name'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_order_field'),
        valueField: 'val',
        editable: true,
        pageSize: 10,
        anchor: '99%',
        allowBlank: true,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.productField.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.productField, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-productfield', shopLogistic.combo.productField);

shopLogistic.combo.orderField = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/order_field/getorder',
        },
        name: 'field',
        hiddenName: 'field',
        fields: ['val', 'name'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_order_field'),
        valueField: 'val',
        editable: true,
        pageSize: 10,
        anchor: '99%',
        allowBlank: true,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.orderField.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.orderField, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-orderfield', shopLogistic.combo.orderField);

shopLogistic.combo.Stores = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        xtype: 'superboxselect',
        allowBlank: true,
        allowAddNewData: true,
        addNewDataOnBlur: false,
        resizable: true,
        name: config.name + '[]',
        anchor: '100%',
        minChars: 2,
        store: new Ext.data.JsonStore({
            id: (config.name || 'properties') + '-store',
            root: 'results',
            autoLoad: true,
            autoSave: false,
            totalProperty: 'total',
            fields: ['name','id'],
            url: shopLogistic.config.connector_url,
            baseParams: {
                action: 'mgr/store/getlist',
            }
        }),
        mode: 'remote',
        displayField: 'name',
        displayFieldTpl: '{name} ({id})',
        valueField: 'id',
        triggerAction: 'all',
        extraItemCls: 'x-tag',
        expandBtnCls: 'x-form-trigger',
        clearBtnCls: 'x-form-trigger',
        renderTo: Ext.getBody(),
    });
    config.name += '[]';
    shopLogistic.combo.Stores.superclass.constructor.call(this,config);
};
Ext.extend(shopLogistic.combo.Stores, Ext.ux.form.SuperBoxSelect);
Ext.reg('shoplogistic-combo-stores', shopLogistic.combo.Stores);

shopLogistic.combo.parser_field_type = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['type', 'display']
            ,data: [
                ['1', 'Внешний']
                ,['2', 'Внутренний']
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'type'
    });
    shopLogistic.combo.parser_field_type.superclass.constructor.call(this,config);
};
Ext.extend(shopLogistic.combo.parser_field_type, MODx.combo.ComboBox);
Ext.reg('combo-parser_field_type', shopLogistic.combo.parser_field_type);

shopLogistic.combo.parser_field_source = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['source', 'display']
            ,data: [
                ['field', 'Поле']
                ,['attribute', 'Атрибут']
                ,['withhtml', 'Поле вместе с HTML']
                ,['css', 'CSS']
                ,['table', 'Таблица']
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'source'
    });
    shopLogistic.combo.parser_field_source.superclass.constructor.call(this,config);
};
Ext.extend(shopLogistic.combo.parser_field_source, MODx.combo.ComboBox);
Ext.reg('combo-parser_field_source', shopLogistic.combo.parser_field_source);

shopLogistic.combo.parser_field_object = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['type', 'display']
            ,data: [
                ['1', 'Категория']
                ,['2', 'Товар']
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'type'
    });
    shopLogistic.combo.parser_field_object.superclass.constructor.call(this,config);
};
Ext.extend(shopLogistic.combo.parser_field_object, MODx.combo.ComboBox);
Ext.reg('combo-parser_field_object', shopLogistic.combo.parser_field_object);

shopLogistic.combo.parserTaskStatus = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/parser/status/getlist',
        },
        name: 'status',
        hiddenName: 'status',
        fields: ['id', 'name', 'description'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_parser_tasks_status'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.parserTaskStatus.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.parserTaskStatus, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-parser-task-status', shopLogistic.combo.parserTaskStatus);

shopLogistic.combo.parserTaskConfig = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        baseParams: {
            action: 'mgr/system/parser/config/getlist',
        },
        name: 'config_id',
        hiddenName: 'config_id',
        fields: ['id', 'name', 'description'],
        mode: 'remote',
        displayField: 'name',
        fieldLabel: _('shoplogistic_parser_tasks_config_id'),
        valueField: 'id',
        editable: true,
        anchor: '99%',
        allowBlank: false,
        autoLoad: true,
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.parserTaskConfig.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.parserTaskConfig, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-parser-config', shopLogistic.combo.parserTaskConfig);