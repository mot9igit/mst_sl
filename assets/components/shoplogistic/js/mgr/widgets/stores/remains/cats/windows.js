shopLogistic.window.CreateStoreRemainsCats = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: "Создание каталога",
        width: 600,
        baseParams: {
            action: 'mgr/storeremains/cats/create',
        },
    });
    shopLogistic.window.CreateStoreRemainsCats.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateStoreRemainsCats, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'store_id',
            id: config.id + '-store_id'
        },{
            xtype: 'statictextfield',
            fieldLabel: "Наименование из Системы Интеграции",
            description: "Изменяется при обмене по API.",
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: "Наименование в системе МСТ",
            description: "Заполните, если необходимо изменить наименование в нашей системе.",
            name: 'name_alt',
            id: config.id + '-name_alt',
            anchor: '99%',
            allowBlank: true,
        },{
            title: 'Флаги публикации',
            html: "<div class='dart-alert dart-alert-info'>На основании этих параметров осуществляется вывод каталога в модуле 'Закупки'.</div>",
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: "Опубликован",
                    description: "Флаг публикации.",
                    name: 'published',
                    id: config.id + '-published',
                    anchor: '99%',
                    checked: true
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: "Активен",
                    description: "Флаг активности. Глобальное отключение.",
                    name: 'active',
                    id: config.id + '-active',
                    anchor: '99%',
                    checked: true,
                }]
            }]
        },{
            title: "Идентификаторы",
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'statictextfield',
                    fieldLabel: "GUID",
                    description: "Поле GUID каталога из Системы Интеграции",
                    name: 'guid',
                    anchor: '99%',
                    id: config.id + '-guid'
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: "GUID БД",
                    description: "Поле GUID базы данных из Системы Интеграции",
                    name: 'base_guid',
                    anchor: '99%',
                    id: config.id + '-base_guid'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'statictextfield',
                    fieldLabel: "GUID родителя",
                    description: "Поле GUID родителя из Системы Интеграции",
                    name: 'parent_guid',
                    anchor: '99%',
                    id: config.id + '-parent_guid'
                }]
            }]
        },{
            xtype: 'textarea',
            fieldLabel: "Описание",
            description: "Описание для команды подключения",
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-storeremains-cats-create', shopLogistic.window.CreateStoreRemainsCats);


shopLogistic.window.UpdateStoreRemainsCats = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/storeremains/cats/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStoreRemainsCats.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStoreRemainsCats, shopLogistic.window.CreateStoreRemainsCats, {

    getFields: function (config) {
        return shopLogistic.window.CreateStoreRemainsCats.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-storeremains-cats-update', shopLogistic.window.UpdateStoreRemainsCats);