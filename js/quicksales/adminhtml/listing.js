listingProducts = Class.create();

listingProducts.prototype = {
    initialize : function(grid, links, container) {

        this.container = $(container);

        this.containerDiv = $(container + '_div');

        this.links = $H(links);

        this.grid = grid;
        this.grid.rowClickCallback = this.rowClick.bind(this);
        this.grid.initRowCallback = this.rowInit.bind(this);
        this.grid.checkboxCheckCallback = this.registerProduct.bind(this); // Associate/Unassociate

        this.grid.rows.each( function(row) {
            this.rowInit(this.grid, row);
        }.bind(this));

    },
    addNewProduct : function(productId, attributes) {
            this.links.set(productId, this.cloneAttributes(attributes));


        this.updateGrid();
        this.updateValues();
        this.grid.reload(null);
    },
    registerProduct : function(grid, element, checked) {
        if (checked) {
            if (element.linkAttributes) {
                this.links.set(element.value, element.linkAttributes);
            }
        } else {
            this.links.unset(element.value);
        }
        this.updateGrid();
        this.updateValues();
    },
    rowClick : function(grid, event) {
        var trElement = Event.findElement(event, 'tr');
        var isInput = Event.element(event).tagName.toUpperCase() == 'INPUT';
        
        if ($(trElement).hasClassName('invalid')) {
            return ;
        }

        if ($(Event.findElement(event, 'td')).down('a')) {
            return;
        }

        if (trElement) {
            var checkbox = $(trElement).down('input');
            if (checkbox && !checkbox.disabled) {
                var checked = isInput ? checkbox.checked : !checkbox.checked;
                grid.setCheckboxChecked(checkbox, checked);
            }
        }
    },
    rowInit : function(grid, row) {
        var checkbox = $(row).down('.checkbox');
        var input = $(row).down('.value-json');
        if (checkbox && input) {
            checkbox.linkAttributes = input.value.evalJSON();

            if (!checkbox.checked) {

                    $(row).removeClassName('invalid');
                    checkbox.enable();
            } else if ($(row).hasClassName('invalid')) {
                checkbox.disable();
                return;
            }
        }
    },
    updateGrid : function() {
        this.grid.reloadParams = {
            'products[]' :this.links.keys().size() ? this.links.keys() : [ 0 ],
            'new_products[]' :this.newProducts
        };
    },
    updateValues : function() {

        var container = this.container;

        while (tr = container.down('tbody tr')) {
            tr.remove();
        }

        var pids = '';

        this.links._each(function(pair) {

            if (!$('assigned_products_' + pair.key)) {
                container.down('tbody').insert(new Template('<tr><td><input type="checkbox" class="no-display" name="listing[assigned_products][]" value="#{id}" checked="checked" /></td><td>#{name}</td></tr>')
                    .evaluate({
                    id: pair.key,
                    name: pair.value.name
                }));
                pids += pair.key + ',';

            }
        });

        categoryId = $('category').value;

        var url = listingSettingsObj.attributesUrl + (listingSettingsObj.attributesUrl.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' )
        var ajax = new Ajax.Updater(
            {success: "assign_attributes"},
            url,
            {
                method: 'post',
                parameters: {categoryId : categoryId, productIds:  pids},
                onComplete: listingSettingsObj.assignEventsToAttributes.bind(this),
                evalScripts: true
            }
        );
    },
    showNoticeMessage : function() {
        $('assign_product_warrning').show();
    }
}

listingSettings = Class.create();

listingSettings.prototype = {
    initialize : function(attributesUrl, attributeValuesUrl) {

        this.attributesUrl = attributesUrl;
        this.attributeValuesUrl = attributeValuesUrl;
//        Event.observe('category','change',this.changeCategory.bind(this));
        this.assignedAttributeChange = this.assignedAttributeChange.bindAsEventListener(this);
        this.assignEventsToAttributes();

    },

    changeCategory : function() {

        var categoryId = 0;
        var categoryLabel = '';
        var level = 0;
        
        $$('[name="listing\[category_selector\]"]').each(function (item) {
            sublevel = item.id.replace('category_selector', '');
            if (sublevel == '') {
                sublevel = 0;
            }
            sublevel = parseInt(sublevel)
            if (sublevel >= level) {
                level = sublevel;
                categoryId = item.value;
                categoryLabel += item.selectedIndex >= 0 ? (item.options[item.selectedIndex].innerHTML + ' / ') : '';
            }
            if (sublevel > 0) {
                item.replace('');
            } else {
                item.value = '';
                item.hide();
            }
        });

        var pids = '';
        for (var i in listingProducts.links.toJSON()) {
            pids += i + ',';
        }

        $('apply_category').hide();

        $('category').value = categoryId;
        $('category_label').update(categoryLabel);

        var url = this.attributesUrl + (this.attributesUrl.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' )
        var ajax = new Ajax.Updater(
            {success: "assign_attributes"},
            url,
            {
                method: 'post',
                parameters: {categoryId : categoryId, productIds:  pids},
                onComplete: this.assignEventsToAttributes.bind(this),
                evalScripts: true
            }
        );

    },
    assignEventsToAttributes : function() {

        elements = $$('select.quicksales_values_association')
        for (var i = 0; i < elements.length; i++) {
            Event.observe(elements[i],'change',this.assignedAttributeChange);
        }

    },
    assignedAttributeChange : function(event) {

        var url = this.attributeValuesUrl + (this.attributeValuesUrl.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' )

        var element = Event.findElement(event, 'select');
        var ajax = new Ajax.Updater(
            {success: element.id.substr(2)},
            url, {
            'method': 'post',
            'parameters': {mAttributeId: element.value, elementName: element.name, category: $F('category')}
        });
    }
}