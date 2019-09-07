/* global Craft */

if (typeof Craft.SproutBase === typeof undefined) {
    Craft.SproutBase = {};
}

Craft.SproutBase.EditAddressModal = Garnish.Modal.extend(
    {
        id: null,
        init: function($addressForm, settings) {

            this.setSettings(settings, Garnish.Modal.defaults);

            this.$form = $('<form class="sprout-address-modal modal fitted" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
            this.$body = $('<div class="body sprout-address-body"></div>').appendTo(this.$form);
            this.$bodyMeta = $('<div class="meta"></div>').appendTo(this.$body);

            this.$addressForm = $addressForm;
            this.$addressFormHtml = $addressForm.html();

            $(this.$addressFormHtml).appendTo(this.$bodyMeta);

            this.modalTitle = Craft.t('sprout-base-fields', 'Update Address');
            this.submitLabel = Craft.t('sprout-base-fields', 'Update');

            // Footer and buttons
            let $footer = $('<div class="footer"/>').appendTo(this.$form);
            let $btnGroup = $('<div class="btngroup left"/>').appendTo($footer);
            let $mainBtnGroup = $('<div class="btngroup right"/>').appendTo($footer);
            this.$updateBtn = $('<input type="button" class="btn submit" value="' + this.submitLabel + '"/>').appendTo($mainBtnGroup);
            this.$footerSpinner = $('<div class="spinner right hidden"/>').appendTo($footer);
            this.$cancelBtn = $('<input type="button" class="btn" value="' + Craft.t('sprout-base-fields', 'Cancel') + '"/>').appendTo($btnGroup);

            this.addListener(this.$cancelBtn, 'click', 'hide');
            this.addListener(this.$updateBtn, 'click', $.proxy(function(ev) {
                ev.preventDefault();

                this.updateAddress();
            }, this));

            this.addListener('.sprout-address-country-select select', 'change', function(ev) {
                this.changeFormInput(ev.currentTarget);
            });

            // Select the country dropdown again for some reason it does not get right value at the form box
            this.$form.find(".sprout-address-country-select select").val(this.settings.countryCode);

            this.base(this.$form, settings);
        },

        changeFormInput: function(target) {

            let $target = $(target);
            let countryCode = $(target).val();
            let $parents = $target.parents('.sprout-address-body');

            Craft.postActionRequest('sprout-base-fields/fields-address/update-address-form-html', {
                countryCode: countryCode,
                namespace: this.settings.namespace
            }, $.proxy(function(response) {
                $parents.find('.sprout-address-onchange-country').remove();

                const $addressIdInput = $parents.find('.sprout-address-id');
                $parents.find('.sprout-address-id').remove();

                if (response.html) {
                    $parents.find('.meta').append(response.html);
                }

                // Add our input back at the bottom
                $parents.find('.meta').append($addressIdInput);
            }, this), [])
        },

        enableUpdateBtn: function() {
            this.$updateBtn.removeClass('disabled');
        },

        disableUpdateBtn: function() {
            this.$updateBtn.addClass('disabled');
        },

        showFooterSpinner: function() {
            this.$footerSpinner.removeClass('hidden');
        },

        hideFooterSpinner: function() {
            this.$footerSpinner.addClass('hidden');
        },

        updateAddress: function() {

            const namespace = this.settings.namespace;

            const formKeys = [
                'fieldId',
                'countryCode',
                'administrativeAreaCode',
                'locality',
                'dependentLocality',
                'postalCode',
                'sortingCode',
                'address1',
                'address2'
            ];

            const formValues = {};

            const self = this;

            $.each(formKeys, function(index, el) {
                formValues[el] = self.$form.find("[name='" + namespace + "[" + el + "]']").val()
            });

            formValues.id = this.settings.addressInfoId;

            const data = {
                formValues: formValues
            };

            if (this.settings.source != null) {
                data.source = this.settings.source;
            }

            data.namespace = this.settings.namespace;

            this.settings.onSubmit(data, $.proxy(function(errors) {

                $.each(errors, function(index, val) {

                    let errorHtml = "<ul class='errors'>";

                    const $element = self.$form.find("[name='" + namespace + "[" + index + "]']");
                    $element.parent().addClass('errors');

                    errorHtml += "<li>" + val + "</li>";
                    errorHtml += "</ul>";

                    if ($element.parent().find('.errors') != null) {
                        $element.parent().find('.errors').remove();
                    }

                    $element.after(errorHtml)
                })
            }))
        },
        defaults: {
            onSubmit: $.noop,
            onUpdate: $.noop
        }
    });
