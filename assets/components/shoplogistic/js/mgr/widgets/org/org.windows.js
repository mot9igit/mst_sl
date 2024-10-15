shopLogistic.window.CreateOrg = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_org_create'),
        width: 1000,
        baseParams: {
            action: 'mgr/org/create',
        },
    });
    shopLogistic.window.CreateOrg.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateOrg, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_org_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_org_description'),
            description: "Описание для команды подключения",
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }, {
            title: 'Роли',
            html: "<div class='dart-alert dart-alert-info'>Данные чекбоксы дают возможность выбора определенных ролей в модуле Аналитики</div>",
            cls: 'def-panel',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_org_warehouse'),
                    description: "Дает возможность Организациям подключаться как к Поставщику и возможность участия Складов в модуле 'Закупки'",
                    name: 'warehouse',
                    id: config.id + '-warehouse',
                    anchor: '99%',
                    checked: false
                }]
            }, {
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_org_store'),
                    description: "Дает возможность участия Складов в модуле 'Маркетплейс'",
                    name: 'store',
                    id: config.id + '-store',
                    anchor: '99%',
                    checked: false
                }]
            }]
        },
        {
            xtype: 'dart-image-field',
            fieldLabel: _('shoplogistic_org_image'),
            description: "Логотип отображается при выборе Поставщика и в кабинете Аналитики",
            name: 'image',
            id: config.id + '-image',
            anchor: '99%'
        }, {
            title: 'Контактное лицо',
            html: "<div class='dart-alert dart-alert-info'>Указывайте контакты ответственного за подключение.</div>",
            cls: 'def-panel',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_org_contact'),
                    name: 'contact',
                    id: config.id + '-contact',
                    anchor: '99%'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_org_email'),
                    name: 'email',
                    id: config.id + '-email',
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_org_phone'),
                    name: 'phone',
                    id: config.id + '-phone',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_org_active'),
            description: "Отключение организации за нарушения Правил",
            name: 'active',
            id: config.id + '-active',
            anchor: '99%',
            checked: true
        }];
    },
});
Ext.reg('shoplogistic-window-org-create', shopLogistic.window.CreateOrg);


shopLogistic.window.UpdateOrg = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('shoplogistic_org_update'),
        width: 1000,
        baseParams: {
            action: 'mgr/org/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateOrg.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateOrg, shopLogistic.window.CreateOrg, {

    // getFields: function (config) {
    //     return shopLogistic.window.CreateOrg.prototype.getFields.call(this, config);
    // }

    getFields: function (config) {

        var default_tabs = [{
            title: _('shoplogistic_org_update'),
            layout: 'form',
            items: shopLogistic.window.CreateOrg.prototype.getFields.call(this, config)
        }, {
            title: _('shoplogistic_org_requisites'),
            items: [{
                xtype: 'shoplogistic-grid-orgrequisites',
                record: config.record,
            }]
        },
        {
            title: _('shoplogistic_stores'),
            items: [{
                xtype: 'shoplogistic-grid-orgstores',
                record: config.record,
            }]
        },
        {
            title: _('shoplogistic_org_users'),
            items: [{
                xtype: 'shoplogistic-grid-orgusers',
                record: config.record,
            }]
        }, {
            title: _('shoplogistic_storebalance'),
            items: [{
                xtype: 'shoplogistic-grid-storebalance',
                record: config.record,
            }]
        }, {
            title: _('shoplogistic_storeregistry'),
            items: [{
                xtype: 'shoplogistic-grid-storeregistry',
                record: config.record,
            }]
        }];

        return [{
            xtype: 'modx-tabs',
            items: default_tabs
        }];
    }

});
Ext.reg('shoplogistic-window-org-update', shopLogistic.window.UpdateOrg);