{switch $item.namespace}
{case '2'}
    <div class="std-notification__content">
        <div class="std-notification__header">
            <div class="std-notification__header-content">
                <i class="std_icon std_icon-notification std-notification__icon"></i>
                <span class="std-notification__span">{$item.date | date_format:"%d.%m.%Y %H:%M"}</span>
                <!--<span class="std-notification__span">12:00</span>-->
            </div></div><div class="std-notification__main">
            <h6 class="std-notification__title">Поступил новый оптовый заказ</h6>
            <p class="std-notification__text">Закупки MachineStore новый оптовый заказ от "{$item.org.name}"</p>
            <div class="std-notification__span">{$item.order.date | date_format:"%d.%m.%Y %H:%M"} вам поступил оптовый заказ от <img class="notificate-image" src="{$item.org.image}" /> {$item.org.name}, {$item.org.address} на сумму {$item.order.cost} ₽.</div>
        </div>
    </div>
{case '7'}
    <div class="std-notification__content">
        <div class="std-notification__header">
            <div class="std-notification__header-content">
                <i class="std_icon std_icon-notification std-notification__icon"></i>
                <span class="std-notification__span">{$item.date | date_format:"%d.%m.%Y %H:%M"}</span>
                <!--<span class="std-notification__span">12:00</span>-->
            </div></div><div class="std-notification__main">
            <h6 class="std-notification__title">Вас добавили в поставщики</h6>
            <p class="std-notification__text">Вы стали поставщиком "{$item.org.name}" на MachineStore</p>
            <div class="std-notification__span">
                <img class="notificate-image" src="{$item.org.image}" />
                {$item.org.name}
                {if $item.org.address}, адрес клиента:{$item.org.address}{/if}
                {if $item.org.inn}, ИНН: {$item.org.inn}{/if}
                {if $item.org.email}, почта: {$item.org.email}{/if}
                {if $item.org.phone}, телефон: {$item.org.phone}{/if} назначил вас своим поставщиком в нашей системе. Не забудьте включить его в акции и настроить для него индивидуальные скидки. Для уточнения деталей свяжитесь с нами:
                <a href="mailto:client.ms@yandex.ru" target="_blank">client.ms@yandex.ru</a>
                <a href="tel:88003501519" target="_blank">8 800 350-15-19</a>
            </div>
        </div>
    </div>
{case '8'}
    <div class="std-notification__content">
        <div class="std-notification__header">
            <div class="std-notification__header-content">
                <i class="std_icon std_icon-notification std-notification__icon"></i>
                <span class="std-notification__span">{$item.date | date_format:"%d.%m.%Y %H:%M"}</span>
                <!--<span class="std-notification__span">12:00</span>-->
            </div></div><div class="std-notification__main">
            <h6 class="std-notification__title">Вас удалили из поставщиков</h6>
            <p class="std-notification__text">Вы перестали быть поставщиком "{$item.org.name}" на MachineStore</p>
            <div class="std-notification__span">
                <img class="notificate-image" src="{$item.org.image}" />
                {$item.org.name}
                {if $item.org.address}, адрес клиента:{$item.org.address}{/if}
                {if $item.org.inn}, ИНН: {$item.org.inn}{/if}
                {if $item.org.email}, почта: {$item.org.email}{/if}
                {if $item.org.phone}, телефон: {$item.org.phone}{/if} исключил вас из числа постащиков на нашей платформе, нам очень жаль . Вы можете связаться с клиентом для выяснения причин.
            </div>
        </div>
    </div>
{case '9'}
    <div class="std-notification__content">
        <div class="std-notification__header">
            <div class="std-notification__header-content">
                <i class="std_icon std_icon-notification std-notification__icon"></i>
                <span class="std-notification__span">{$item.date | date_format:"%d.%m.%Y %H:%M"}</span>
                <!--<span class="std-notification__span">12:00</span>-->
            </div></div><div class="std-notification__main">
            <h6 class="std-notification__title">Ваш склад отключен</h6>
            <p class="std-notification__text">MachineStore потерял связь с вашим складом "{$item.store.name_short}", {$item.store.address}</p>
            <div class="std-notification__span">
                Мы перестали получать информацию о ваших остатках и ценах, теперь клиенты не могут получить к ним доступ. Мы уже занимаемся решением данной проблемы. Для уточнения деталей свяжитесь с нами:
                <a href="mailto:client.ms@yandex.ru" target="_blank">client.ms@yandex.ru</a>
                <a href="tel:88003501519" target="_blank">8 800 350-15-19</a>
            </div>
        </div>
    </div>
{case '10'}
    <div class="std-notification__content">
        <div class="std-notification__header">
            <div class="std-notification__header-content">
                <i class="std_icon std_icon-notification std-notification__icon"></i>
                <span class="std-notification__span">{$item.date | date_format:"%d.%m.%Y %H:%M"}</span>
                <!--<span class="std-notification__span">12:00</span>-->
            </div></div><div class="std-notification__main">
            <h6 class="std-notification__title">Ваш склад подключен</h6>
            <p class="std-notification__text">MachineStore восстановил связь с вашим складом "{$item.store.name_short}", {$item.store.address}</p>
            <div class="std-notification__span">
                Мы получаем информацию о ваших остатках и ценах, теперь клиенты могут получить к ним доступ. Для уточнения деталей свяжитесь с нами:
                <a href="mailto:client.ms@yandex.ru" target="_blank">client.ms@yandex.ru</a>
                <a href="tel:88003501519" target="_blank">8 800 350-15-19</a>
            </div>
        </div>
    </div>
{/switch}