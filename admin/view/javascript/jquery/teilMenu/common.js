(function(){
    var editForm = {
        init: function(){
            this.token = $('#token_key').val();
            this.content = "";
            this.menu_id = $('#current_menu_id').val();
            this.registerEvents();
        },
        
        showForm: function(e){
            editForm.id = $(this).closest('li').attr('id').replace('list_', '');
            
            if ( ! $('.isEditing').length)
                editForm.getInfo.call(this);
            
            e.preventDefault();
        },
        
        getInfo: function(){
            var self = this,
                content = "",
                url = '/admin/index.php?route=design/menu_ajax&token=' + editForm.token,
                data = {
                    method: 'get_item_info',
                    id: editForm.id
                }
            
            // Store id var to save data
            editForm.active_id = editForm.id;
            
            $.post(url, data, function(data){
                // Loading html form
                editForm.loadHtml(self, data);
                
                // Calcel
                $('#form_calcel').on('click', function(e){
                    $('#form_table').remove();
                    $('.isEditing').removeClass('isEditing');
                    
                    e.preventDefault();
                });
                
                // Save
                $('#form_save').on('click', function(e){
                    editForm.sendData();
                    
                    e.preventDefault();
                });
            }, 'json');
        },
        
        loadHtml: function($this, data){
            var self = $this,
                html = "<tr class='editableContent'><td>Ссылка</td><td><input id='the_href' type='text' value='{{href}}'></td></tr></td></tr><tr><td><a href='#' id='form_save' class='button'>Save</a></td><td><a href='#' id='form_calcel' class='button'>Cancel</a></td></tr></table></form>"
            
            // Append html
            editForm.template += html;
            
            // Load template
            editForm.content += editForm.template
                                .replace( /{{name}}/ig, data.name )
                                .replace( /{{href}}/ig, data.href )
                                .replace( /{{title}}/ig, data.title )
            
            // Add form
            $(self)
                .closest('li')
                .addClass('isEditing')
                .children('div')
                .after(editForm.content)
            
            $('#link_type').on('change', function(){
                selected = $(this).val();
                
                switch (selected)
                {
                    case 'link':
                        html = "<td>Ссылка</td><td><input id='the_href' type='text' value='{{href}}'></td></tr></td>";
                    break;
                    case 'category':
                        html = "CATEGORY";
                    break;
                    case 'product':
                        
                    break;
                    case 'file':
                        
                    break;
                }
                $('.editableContent').html(html);
                
                // Ending
                html += "<tr><td><a href='#' id='form_save' class='button'>Save</a></td><td><a href='#' id='form_calcel' class='button'>Cancel</a></td></tr></table></form>";
                
            });
        },
        
        sendData: function(){
            var self = this,
                url = '/admin/index.php?route=design/menu_ajax&token=' + editForm.token,
                name = $('#the_name').val(),
                data = {
                    method: 'update_item_info',
                    id: editForm.active_id,
                    name: name,
                    href: $('#the_href').val(),
                    title: $('#the_title').val()
                }
            
            // Send data
            $.post(url, data, function(){
                
                //Change menu item heading and remove edit form
                $('#list_' + editForm.active_id)
                    .find(' > div > span')
                    .text(name)
                    .end()
                    .find('#form_table')
                    .remove()
                    
                // Enable edditing other menu items
                $('.isEditing').removeClass('isEditing');
            });
            
        },
        
        addMenuItem: function(e){
            var url = '/admin/index.php?route=design/menu_ajax&token=' + editForm.token + '&id=' + editForm.menu_id,
                data = {
                    method: 'create_menu_item'
                },
                template = ""
                
            $.post(url, data, function(data){
                template = editForm.new_item_template
                                .replace( /{{id}}/ig, data.id )
                
                $('#sortable').append(template);
                $('#list_' + data.id).find('.edit_menu_item').on('click', editForm.showForm)
            }, 'json');
            
            e.preventDefault();
        },
        
        deleteItem: function(e){
            var id = $(this).closest('li').attr('id').replace('list_', '');
            
            if (confirm("Удалить?"))
            {
                parent.location = '/admin/index.php?route=design/menu/deleteMenuItem&token=' + editForm.token + '&id=' + id;
            }
            
            e.preventDefault();
        },
        
        registerEvents: function(){
            var self = this;
            
            $('#sortable .edit_menu_item').each(function(){
                $(this).on('click', self.showForm);
            });
            
            
            $('#sortable .delete_menu_item').each(function(){
                $(this).on('click', self.deleteItem);
            });
            
            $('#create_menu_item').on('click', self.addMenuItem);
        },
        
        onLinkType: function(){
            var selected,
                html = "<tr class='editableContent'><td>Ссылка</td><td><input id='the_href' type='text' value='{{href}}'></td></tr></td></tr><tr><td><a href='#' id='form_save' class='button'>Save</a></td><td><a href='#' id='form_calcel' class='button'>Cancel</a></td></tr></table></form>"
            
            $('#link_type').on('change', function(){
                $('.editableContent').remove();
                selected = $(this).val();
                
                switch (selected)
                {
                    case 'link':
                        html = "<tr><td>Ссылка</td><td><input id='the_href' type='text' value='{{href}}'></td></tr></td></tr>";
                        // Ending
                        html += "<tr><td><a href='#' id='form_save' class='button'>Save</a></td><td><a href='#' id='form_calcel' class='button'>Cancel</a></td></tr></table></form>";
                    break;
                    case 'category':
                        html = "CATEGORY";
                    break;
                    case 'product':
                        
                    break;
                    case 'file':
                        
                    break;
                }
            });
            
            // Edit template
            editForm.template += html;
        },
        
        template: "<form id='form_table'><table><tr><td>Название</td><td><input id='the_name' type='text' value='{{name}}'></td></tr><tr><td>Тайтл</td><td><input id='the_title' type='text' value='{{title}}'></td></tr><tr><td>Тип ссылки</td><td><select id='link_type'><option value='link'>Ссылка</option><option value='category'>Категория</option><option value='product'>Товар</option><option value='file'>Файл</option></select>",
        new_item_template: "<li id='list_{{id}}'><div><span>Новый пункт меню</span><a href='#' class='fR'>[ <span>Удалить</span> ]</a><a href='#' class='edit_menu_item fR'>[ <span>Изменить</span> ]</a></div></li>"
    };
    
    // Edit form initialixation
    editForm.init();
})();

// Submit to delete menu recursively
function onDeleteMenu(url)
{
    if (confirm('Удаляя меню вы удаляете ВСЕ пункты меню! Удалить?'))
    {
        window.location = url;
    }
}