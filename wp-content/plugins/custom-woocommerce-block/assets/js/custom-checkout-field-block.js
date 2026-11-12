(function (blocks, element) {
    const { registerBlockType } = blocks;
    const { TextControl } = wp.components;
    const { useSelect, useDispatch } = wp.data;

    registerBlockType('custom/checkout-gift-message', {
        title: 'Gift Message',
        icon: 'edit',
        category: 'woocommerce',
        edit: function () {
            const { setBillingData } = useDispatch('wc/blocks-checkout');
            const billingData = useSelect((select) => select('wc/blocks-checkout').getBillingData());

            return element.createElement(
                'div',
                { className: 'custom-gift-message' },
                element.createElement(TextControl, {
                    label: 'Gift Message',
                    value: billingData.gift_message || '',
                    onChange: (value) => {
                        setBillingData({ ...billingData, gift_message: value });
                    },
                    placeholder: 'Enter your gift message',
                })
            );
        },
        save: () => {
            return null; // Rendered server-side
        }
    });
})(window.wp.blocks, window.wp.element);
