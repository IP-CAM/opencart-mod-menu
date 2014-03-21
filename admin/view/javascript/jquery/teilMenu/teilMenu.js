/*
 * Author: Yurii Krevnyi
 * 
 **/

// PubSub
(function( $ ) {
    var o = $( {} );

    $.each({
        trigger: 'publish',
        on: 'subscribe',
        off: 'unsubscribe'
    }, function( key, val ) {
        jQuery[val] = function() {
            o[key].apply( o, arguments );
        };
    });
})( jQuery );

// The plugin
var teilMenu = {
    init: function(config) {
        this.config = config;
        this.listCategories = this.listProducts = this.listManufacturers = 0; // Default catalog data bug fix

        this.handlebars();

        this.bindEvents();
        this.subscriptions();
    },
    
    // Adding event listeners to menu item EDIT, DELETE, CREATE buttons
    bindEvents: function() {
        this.config.container.find('.edit_menu_item').on('click', this.onMenuItemClickHandler);
        this.config.container.find('.delete_menu_item').on('click', this.deleteMenuItem);

        $('#create_menu_item').on('click', this.createMenuItem);
    },
    
    // PubSub dispached events
    subscriptions: function() {
        $.subscribe('item/get/information', this.showItemInfo);
        $.subscribe('item/html/updated', this.itemLoaded);
        $.subscribe('item/updated/information', this.closeForm);
    },
    
    // Prepare handlebars AND register helper functions
    handlebars: function () {
        this.template = Handlebars.compile(this.config.template);
        this.itemTemplate = Handlebars.compile(this.config.itemTemplate);

        // Escaping decoded html (>, ")
        Handlebars.registerHelper('escaped', function(data) {
            return data
                .replace(/&gt;/ig, '>')
                .replace(/&quot;/ig, '"');
        });

        // Target checkbox (_self, _parent, _blank)
        Handlebars.registerHelper('pageTarget', function(data) {
            if (data == 1)
                return 'checked="checked"'
        });

        // Used for multilanguage
        Handlebars.registerHelper('getLangValue', function(data, option) {
            var name = '';

            $.each(data, function(index, obj){
                if (index == option)
                    name = obj;
            });

            return name;
        });

        // Check if user want to se developer options
        Handlebars.registerHelper('developerMode', function(options) {
            var devMode = window.localStorage.teilMenuDeveloperMode || 0;

            if (devMode)
            {
                return true;
            }

            return false;
        });
    },

    // Creates new menu item
    createMenuItem: function (e) {
        var self = teilMenu

        $.ajax({
            url: '/admin/index.php?route=design/menu_ajax&token=' + self.config.token,
            dataType: 'json',
            type: 'POST',
            data: {
                method: 'create_menu_item',
                menuId: self.config.menuId
            },
            success: function(results) {
                // Store height
                var height = $('#sortable')
                    .append(self.itemTemplate(results))
                    .children('li:last')
                    .height()

                // Slide down new menu item, add event listener to it
                $('#sortable > li:last')
                    .height(0)
                    .animate({height: height}, function(){$(this).height('auto')})
                    .find('.edit_menu_item')
                    .on('click', self.onMenuItemClickHandler)
                    .end()
                    .find('.delete_menu_item')
                    .on('click', self.deleteMenuItem)
            }
        });

        e.preventDefault();
    },

    // Delete selected menu item
    deleteMenuItem: function (e) {
        var self = teilMenu,
            itemArrt = $(this).closest('li').attr('id'),
            itemId = itemArrt.split('_').pop()

        if ( ! confirm(self.config.delete_menu_item_confirm))
            return false;

        $.ajax({
            url: '/admin/index.php?route=design/menu_ajax&token=' + self.config.token,
            dataType: 'json',
            type: 'POST',
            data: {
                method: 'delete_menu_item',
                id: itemId
            },
            success: function(results) {
                // On delete slide up AND delete localy
                $('#' + itemArrt).slideUp(function(){
                    $(this).remove();
                })
            }
        });

        e.preventDefault();
    },

    // On menu item EDIT button handler
    onMenuItemClickHandler: function(e) {
        var self = teilMenu,
            $this = $(this),
            parentLi = $this.closest('li')
        
        // Prevent for opening more than 1 item
        if ($('#sortable li').hasClass('isEditing'))
            return false;

        // Store current li
        self.currentItem = parentLi.addClass('isEditing').children('div');
        self.currentItemId = parentLi.attr('id').split('_').pop()

        // Efects
        $this.addClass('hidden').siblings('.teilLoader').show();
        
        // Checks for existing listCategories, listManufacturers...
        if ( ! self.listCategories)
        {
            self.getCatalogInfo($this);
            return false;
        }

        // Get item information
        self.getItemInformation($this);
        
        e.preventDefault();
    },

    // Get static catalog information(categories, products, manufacturers, information...)
    getCatalogInfo: function (button) {
        var self = this,
            $this = button

        $.ajax({
            url: '/admin/index.php?route=design/menu_ajax&token=' + self.config.token,
            dataType: 'json',
            type: 'POST',
            data: {
                method: 'get_catalog_info'
            },
            success: function(results) {
                // Store catalog info(categories, products...)
                self.listCategories = results.categories;
                self.listManufacturers = results.manufacturers;
                self.listProducts = self.objToArray(results.products);
                self.listInformation = results.information;

                // And get item clicked info
                self.getItemInformation($this);
            }
        });
    },

    // Gets information of clicked item
    getItemInformation: function (button) {
        var self = teilMenu,
            $this = button

        $.ajax({
            url: '/admin/index.php?route=design/menu_ajax&token=' + self.config.token,
            dataType: 'json',
            type: 'POST',
            data: {
                method: 'get_item_info',
                id: self.currentItemId
            },
            success: function(results) {
                // Efects
                $this.removeClass('hidden').siblings('.teilLoader').hide();
                
                // Show up new menu item
                self.itemInfo = results;
                $.publish('item/get/information');
            }
        });
    },
    
    // Prepare new html to be appended
    showItemInfo: function() {
        var self = teilMenu,
            itemInfo = self.itemInfo,
            itemType = parseInt(itemInfo.type),
            itemHref = itemInfo.href,

            // Set default selected option
            _setSelectedItem = function (selector, id) {
                $.each(selector, function() {
                    if ($(this).val() == id)
                        $(this).attr('selected', 'selected');
                });
            }
        
        
        switch(itemType)
        {
            case 0:
                // Typical link
                self.updateHtml({
                    item: itemInfo,
                    link: 'link'
                });
            break;
            case 1:
                // Typical category
                var categoryId = self.getIdFromHref('&path=', itemHref);
                itemInfo.href = 'http://'; // Href bug fixed

                self.updateHtml({
                    item: itemInfo,
                    categoryId: categoryId
                });

                _setSelectedItem($(".category select option"), categoryId);
            break;
            case 2:
                // Typical manufactorer
                var manufactorerId = self.getIdFromHref('&manufacturer_id=', itemHref);
                itemInfo.href = 'http://'; // Href bug fixed
                
                self.updateHtml({
                    item: itemInfo,
                    manufactorerId: manufactorerId
                });

                _setSelectedItem($(".manufacturer select option"), manufactorerId);
            break;
            case 3:
                // Typical product
                var productId = self.getIdFromHref('&product_id=', itemHref);
                itemInfo.href = 'http://'; // Href bug fixed
                
                self.updateHtml({
                    item: itemInfo,
                    productId: productId
                });

                _setSelectedItem($(".product select option"), productId);
            break;
            case 4:
                // Typical information
                var informationId = self.getIdFromHref('&information_id=', itemHref);
                itemInfo.href = 'http://'; // Href bug fixed
                
                self.updateHtml({
                    item: itemInfo,
                    informationId: informationId
                });

                _setSelectedItem($(".information select option"), informationId);
            break;
        }
    },
    
    // Append menu item FORM
    updateHtml: function(data) {
        var self = teilMenu,
            // Object allow us to use handlebars quite comfortable
            catalogInfo = {
                categories: self.listCategories,
                manufacturers: self.listManufacturers,
                products: self.listProducts,
                information: self.listInformation
            },

            // Handlebars creates template
            _appendForm = function () {
                var form = self.currentItem.after(self.template(data)).parent().find('#form_table'),
                    height = 0;

                if (data.item.view_type) {
                    form.find('#link_view_type .' + data.item.view_type)
                        .attr('selected', 'selected');
                };
            }
        
        // Append new form to current menu item
        $.extend(data, catalogInfo);
        _appendForm();

        // Save data on enter key
        $('#form_table').on('keyup', function(e) {
            if (e.keyCode == '13') { $('#form_save').trigger('click'); };
        });

        // Event listeners for SAVE, CANCEL form buttons
        $('#form_save').on('click', self.save);
        $('#form_calcel').on('click', self.closeForm);

        $.publish('item/html/updated');
    },
    
    // Triggerd after FORM is loaded ( func updateHtml() )
    itemLoaded: function() {
        var self = teilMenu;

        // Change listener to 1st select (link type)
        $('#link_type').on('change', self.onChangeItemType);

        // Change listener to link view type
        $('#link_view_type').on('change', self.onChangeItemViewType).trigger('change');

        // Store height
        self.slideItemDown();
    },

    // Slide down edit form
    slideItemDown: function () {
        var $form = $('#form_table'),
            height = $form.height();

        // Animate form height
        $form.height(0)
            .animate({height: height}, 300);
    },
    
    // Trigger after user changes link type(1st select)
    onChangeItemType: function() {
        var selected = $(this).val(),
            container = $('#form_table'),
            _show = function(target) {
                container
                    .find('tr.link, tr.category, tr.manufacturer, tr.product, tr.information')
                    .addClass('hidden')
                    .end()
                    .find('tr.' + target)
                    .removeClass('hidden')
            }
        
        switch(selected)
        {
            case 'link':
                _show('link');
            break;
            case 'category':
                _show('category');
            break;
            case 'manufacturer':
                _show('manufacturer');
            break;
            case 'product':
                _show('product');
            break;
            case 'information':
                _show('information');
            break;
        }

        // Update edit form size
        teilMenu.updateSizes();
    },

    // Trigger after user changes link view type
    onChangeItemViewType: function() {
        var $this = $(this),
            selected = $this.val(),
            container = $('#form_table'),
            _show = function(target) {
                container
                    .find('.depending-field')
                    .addClass('hidden')
                    .end()
                    .find('tr.' + target)
                    .removeClass('hidden');

                // If current item view is link or banner
                // We are going to trigger change on item type
                // In that case item type will display correctly
                if (selected.match(/^(link|banner)$/i)) {
                    $('#link_type').trigger('change');
                };
            }
        
        _show($this.find('option:checked').data('show-dependings'));

        // Update edit form size
        teilMenu.updateSizes();
    },
    
    // Prepare menu item information to be saved
    save: function (e) {
        var self = teilMenu,
            select = $('#link_type'),
            selectView = $('#link_view_type'),
            linkType = '',
            theData = {
                id: self.currentItemId,
                linkViewType: selectView.val()
            },

            // Returns current selected option
            _idFinder = function (type) {
                return select
                    .closest('tr')
                    .siblings('.' + type)
                    .find('select:first option:selected')
                    .val()
            },

            // Get name of current language
            _getName = function (data) {
                var arr = data.split('&'),
                    name = ''

                $.each(arr, function (index, obj) {
                    var lang = 'language_' + self.config.adminLang + '=',
                        pattenr = new RegExp('^' + lang + '')

                    if (obj.match(pattenr))
                        name = obj.replace(lang, '');
                });

                return name;
            };

        // Clear old input
        self.clearOldFormData(
            selectView.find('option:selected').data('show-dependings')
        );

        // Get link type
        linkType = select.val();

        // Get the linkType, get selected option id
        switch (linkType)
        {
            case 'link':
                theData.linkType = 0;
                theData.itemId = _idFinder('link');
            break;
            case 'category':
                theData.linkType = 1;
                theData.itemId = _idFinder('category');
            break;
            case 'manufacturer':
                theData.linkType = 2;
                theData.itemId = _idFinder('manufacturer');
            break;
            case 'product':
                theData.linkType = 3;
                theData.itemId = _idFinder('product');
            break;
            case 'information':
                theData.linkType = 4;
                theData.itemId = _idFinder('information');
            break;
        }

        // Efects
        $(this).hide().siblings('img').show();

        // Sends data
        $.extend(theData, self.getFormInfo());
        self.sendData(theData);

        // Updates local heading of current menu item
        self.currentItem.children('span').html(_getName(theData.name));

        e.preventDefault();
    },

    // Send data to server via AJAX
    sendData: function (theData) {
        var self = teilMenu

        $.ajax({
            url: '/admin/index.php?route=design/menu_ajax&token=' + self.config.token,
            dataType: 'json',
            type: 'POST',
            data: {
                method: 'save_data',
                data: theData
            },
            success: function(results) {
                $.publish('item/updated/information');
            }
        });
    },

    // Update edit form size
    updateSizes: function () {
        var $form = $('#form_table'),
            formHeight = $form.children('table').outerHeight();

        $form.height(formHeight);
    },

    // Just closes the form
    closeForm: function (e) {
        var self = teilMenu,
            currentItem = self.currentItem,
            form = currentItem.siblings('#form_table'),
            item = currentItem.closest('.isEditing'),
            borderColor = ''

        form.slideUp(300, function () {
            $(this).remove();
        })
        item.removeClass('isEditing');

        // IF clicked close btn, we should prevent default event
        if (e) e.preventDefault();
    },

    // Returns user inputed values
    // Prepare names to diffrent langs (language_1=>name1&language_2=>name2)
    getFormInfo: function () {
        return {
            name: this.getValueList('.form_name'),
            title: this.getValueList('.form_title'),
            image: $('#image').val(),
            href: $('#form_href').val(),
            params: $('#form_params').val(),
            self_class: $('#form_self_class').val(),
            target: ~~ !! $('#form_target').attr('checked') // Convert to bool, then to int
        }
    },

    // Clear old input
    // ex. User created new banner menu item, seved it and then change it to heading
    // So, now we should clear item `image`, `href`, etc fileds
    clearOldFormData: function (dependingType) { 
        console.log(dependingType);
        $('#form_table .depending-field:not(.' + dependingType + ')')
            .find('input, select, input[type=checkbox]')
            .val('')
            .removeAttr('checked')
            .removeAttr('selected');
    },

    // Get name, title list by difrent langs
    getValueList: function (selector) {
        var values = '',
            items = $(selector),
            lang = 0;

        $.each(items, function(index, obj) {
            lang = $(this).data('lang');

            values += 'language_' + lang + '=' + $(this).val()

            if (index < items.length - 1)
                values += '&'
        });
        
        return values;
    },

    // Get the last number from href
    // Example:
    // /index.php?route=product/category&path=_101_2_26 ===> 26
    getIdFromHref: function(separator, href) {
        var id = href.split(separator)[1];

        console.log(id);
        // If ID like 20_31_73 ---> get last number
        if (id.split('_').length > 1)
        {
            id = id.split('_');
            id = id[id.length - 1];
        }
        
        return id;
    },

    // Convert obj to array
    objToArray: function (obj) {
        var array = $.map(obj, function(k, v) {
            return [k];
        });

        return array;
    }
};

// Submit to delete menu recursively
function onDeleteMenu(url)
{
    if (confirm(teilMenu.config.delete_menu_confirm))
    {
        window.location = url;
    }
}