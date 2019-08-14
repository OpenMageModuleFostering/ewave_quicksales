CategoryActions = Class.create();
CategoryActions.prototype = {
    initialize: function(categoryObjectId, url) {

        this.categoryObjectId = categoryObjectId;
        this.url = url;

        if (categoryObjectId == 'category_selector') {
            $(categoryObjectId).hide();
        }

        Event.observe(this.categoryObjectId, 'change', this.changeCategory.bind(this));
    },

    changeCategory : function(event) {

        var element = Event.findElement(event, 'select');

        new Ajax.Request(this.url, {
                'method': 'post',
                'parameters': {parentId: element.value},
                'onSuccess': function(transport) {
                    var subcategories = transport.responseText.evalJSON();
                    if (subcategories.length > 0) {

                        var select = new Element("select");

                        select.name = element.name;

                        level = element.id.replace('category_selector', '');
                        if (level == '') {
                            level = 0;
                        }

                        level++;

                        $$('[name="' + element.name + '"]').each(function(item) {
                            sublevel = item.id.replace('category_selector', '');
                            if (sublevel != '' && parseInt(sublevel) >= parseInt(level)) {
                                $(item).replace('');
                            }
                        })

                        select.id = 'category_selector' + level;

                        var option = document.createElement("option");
                        option.text = '';
                        option.value = '';
                        select.options.add(option, 0);

                        for (i = 0; i < subcategories.length; i++) {
                            var option = document.createElement("option");
                            option.text = subcategories[i]['label'];
                            option.value = subcategories[i]['value'];

                            select.options.add(option, select.options.length);
                        }

                        Element.insert(element, {after : select});
						
						var br = new Element("br");
						
						Element.insert(element, {after : br});

                        new CategoryActions(select, transport.request.url);

						$('apply_category').hide();
                    } else {
                        $('apply_category').show();

                    }
                }
            }
        );

    }

};

