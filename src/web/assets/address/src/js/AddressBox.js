/* global Craft */

if (typeof Craft.SproutBase === typeof undefined) {
    Craft.SproutBase = {};
}

(function($) {

    // Set all the standard Craft.SproutBase.* stuff
    $.extend(Craft.SproutBase,
        {
            initFields: function($container) {
                $('.sproutaddressinfo-box', $container).SproutAddressBox();
            }
        });

    // -------------------------------------------
    //  Custom jQuery plugins
    // -------------------------------------------

    $.extend($.fn,
        {
            SproutAddressBox: function() {
                const $container = $(this);
                return this.each(function() {
                    new Craft.SproutBase.AddressBox($container);
                });
            }
        });

    Craft.SproutBase.AddressBox = Garnish.Base.extend({

        $addressBox: null,

        $addButtons: null,
        $editButtons: null,
        $addressFormat: null,

        $addButton: null,
        $updateButton: null,
        $clearButton: null,
        $queryButton: null,

        addressId: null,
        addressInfo: null,
        $addressForm: null,
        countryCode: null,
        // actionUrl: null,
        $none: null,
        modal: null,

        init: function($addressBox, settings) {

            this.$addressBox = $addressBox;

            this.$addButton = this.$addressBox.find('.address-add-button a');
            this.$updateButton = this.$addressBox.find('.address-edit-buttons a.update-button');
            this.$clearButton = this.$addressBox.find('.address-edit-buttons a.clear-button');
            this.$queryButton = $('.query-button');

            this.$addButtons = this.$addressBox.find('.address-add-button');
            this.$editButtons = this.$addressBox.find('.address-edit-buttons');
            this.$addressFormat = this.$addressBox.find('.address-format');

            this.settings = settings;

            if (this.settings.namespace == null) {
                this.settings.namespace = 'address';
            }

            this.addressId = this.$addressBox.data('addressId');

            this.renderAddress();

            this.addListener(this.$addButton, 'click', 'editAddressBox');
            this.addListener(this.$updateButton, 'click', 'editAddressBox');
            this.addListener(this.$clearButton, 'click', 'clearAddressBox');
            this.addListener(this.$queryButton, 'click', 'queryAddressCoordinatesFromGoogleMaps');
        },

        renderAddress: function() {

            if (this.addressId === '' || this.addressId == null) {
                this.$addButtons.removeClass('hidden');
                this.$editButtons.addClass('hidden');
                this.$addressFormat.addClass('hidden');
            } else {
                this.$addButtons.addClass('hidden');
                this.$editButtons.removeClass('hidden');
                this.$addressFormat.removeClass('hidden');
            }

            this.$addressForm = this.$addressBox.find('.sproutfields-address-formfields');

            this.getAddressFormFields();
        },

        editAddressBox: function(event) {
            event.preventDefault();

            let source = null;

            if (this.settings.source != null) {
                source = this.settings.source;
            }

            this.$target = $(event.currentTarget);

            let countryCode = this.$addressForm.find('.sprout-address-country-select select').val();

            this.modal = new Craft.SproutBase.EditAddressModal(this.$addressForm, {
                onSubmit: $.proxy(this, 'getAddressDisplayHtml'),
                countryCode: countryCode,
                namespace: this.settings.namespace,
                source: source
            }, this.$target);

        },

        getAddressDisplayHtml: function(data) {

            const self = this;

            /**
             * @param {string} response.countryCodeHtml
             * @param {string} response.addressFormHtml
             * @param {Array} response.errors
             */
            Craft.postActionRequest('sprout-base-fields/fields-address/get-address-display-html', data, $.proxy(function(response) {
                if (response.result === true) {

                    this.$addressBox.find('.address-format').html(response.html);
                    self.$addressForm.empty();
                    self.$addressForm.append(response.countryCodeHtml);
                    self.$addressForm.append(response.addressFormHtml);

                    self.$addButtons.addClass('hidden');
                    self.$editButtons.removeClass('hidden');
                    self.$addressFormat.removeClass('hidden');

                    this.modal.hide();
                    this.modal.destroy();
                } else {
                    Garnish.shake(this.modal.$form);
                    let errors = response.errors;
                    $.each(errors, function(key, value) {
                        $.each(value, function(key2, value2) {
                            Craft.cp.displayError(Craft.t('sprout-base-fields', value2));
                        });
                    });
                }

            }, this), []);
        },

        getAddressFormFields: function() {

            const self = this;

            let addressId = this.$addressBox.data('addressId');
            let defaultCountryCode = this.$addressBox.data('defaultCountryCode');
            let showCountryDropdown = this.$addressBox.data('showCountryDropdown');

            Craft.postActionRequest('sprout-base-fields/fields-address/get-address-form-fields-html', {
                addressId: addressId,
                defaultCountryCode: defaultCountryCode,
                showCountryDropdown: showCountryDropdown,
                namespace: this.settings.namespace
            }, $.proxy(function(response) {
                this.$addressBox.find('.address-format .spinner').remove();
                self.$addressBox.find('.address-format').empty();
                self.$addressBox.find('.address-format').append(response.html);
            }, this), []);
        },

        clearAddressBox: function(event) {
            event.preventDefault();

            const self = this;

            this.$addButtons.removeClass('hidden');
            this.$editButtons.addClass('hidden');
            this.$addressFormat.addClass('hidden');
            this.$addressForm.find("[name='" + this.settings.namespace + "[delete]']").val(1);

            self.addressId = null;

            this.$addressBox.find('.sprout-address-onchange-country').remove();

            this.emptyForm();
            this.getAddressFormFields();
        },

        emptyForm: function() {

            const formKeys = [
                'countryCode',
                'administrativeArea',
                'locality',
                'dependentLocality',
                'postalCode',
                'sortingCode',
                'address1',
                'address2'
            ];

            const self = this;

            $.each(formKeys, function(index, el) {
                self.$addressBox.find("[name='" + self.settings.namespace + "[" + el + "]']").attr('value', '')
            });
        },

        queryAddressCoordinatesFromGoogleMaps: function(event) {

            event.preventDefault();

            const self = this;
            const spanValues = [];

            let $addressFormat = $(".address-format");

            $addressFormat.each(function() {
                spanValues.push($(this).text());
            });

            self.addressInfo = spanValues.join("|");

            if ($addressFormat.is(':hidden')) {
                Craft.cp.displayError(Craft.t('sprout-base-fields', 'Please add an address'));
                return false;
            }

            const data = {
                addressInfo: self.addressInfo
            };

            /**
             * @param {JSON} response[].geo
             * @param {Array} response.errors
             */
            Craft.postActionRequest('sprout-base-fields/fields-address/query-address-coordinates-from-google-maps', data, $.proxy(function(response) {
                if (response.result === true) {
                    const latitude = response.geo.latitude;
                    const longitude = response.geo.longitude;
                    // @todo - add generic name?
                    $("input[name='sproutseo[globals][identity][latitude]']").val(latitude);
                    $("input[name='sproutseo[globals][identity][longitude]']").val(longitude);

                    Craft.cp.displayNotice(Craft.t('sprout-base-fields', 'Latitude and Longitude updated.'));
                } else {
                    Craft.cp.displayError(Craft.t('sprout-base-fields', 'Unable to find the address: ' + response.errors));
                }
            }, this), [])
        }
    })
})(jQuery);
