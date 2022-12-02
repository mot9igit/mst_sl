<div class="mspc_form">
    <div class="form-group sl-row">
        <div class="d-col-12">
            <div class="sl-input-group">
                <input type="text" class="mspc_field base_input [[+coupon:notempty=`[[+disfield]]`]]" [[+coupon:notempty=`disabled`]] value="[[+coupon]]" placeholder="[[%mspromocode_enter_promocode]]" />
                <div class="sl-input-group-append">
                    <button class="mspc_btn sl-btn sl-btn-outline-secondary" type="submit">[[+btn]]</button>
                </div>
            </div>
            <div class="mspc_msg"></div>
        </div>
    </div>
    <ul class="summary-summation">
        <li class="mspc_discount_amount" style="display: none;">
            <div class="summary-summation__term ">[[%mspromocode_discount_amount]]</div>
            <div class="summary-summation__data ">[[+discount_amount]]&thinsp;₽</div>
        </li>
    </ul>
</div>