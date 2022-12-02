<div class="modal fade" id="modal_city" tabindex="-1" aria-labelledby="city_title" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="city_title">Выберите ваш город</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="city_choice">
                    <input type="text" name="city" class="form-control city_complete" placeholder="Начните вводить название города">
                </div>
                {$_modx->runSnippet("!sl.get_cities", [
                "tpl" => "@FILE chunks/sl_cities.tpl"
                ])}
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal_store" tabindex="-1" aria-labelledby="store_title" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="store_title">Выберите ваш магазин</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="store_choice">
                    <input type="text" name="store" class="form-control store_complete" placeholder="Начните вводить название магазина">
                </div>
                {$_modx->runSnippet("!sl.get_stores", [
                "tpl" => "@FILE chunks/sl_check_stores.tpl"
                ])}
            </div>
        </div>
    </div>
</div>