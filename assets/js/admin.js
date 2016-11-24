(function($, _, ajaxUrl){
  
  $.fn.exchangeWith = function( $el ){
    if(this.length != 1) return this;
    if(! $el instanceof $){
      $el = $($el);
    }
    if($el.length != 1) return this;
    this.after($el);
    this.detach();
    return $el;
  };
  
  $.fn.extractFormData = function(){
    if(this.length != 1) return false;
    var data = {};
    this.find('input, select, textarea, output').filter('[name]').each(function(){
      var name = $(this).attr('name');
      if(name.endsWith('[]')){
        if(!Array.isArray(data[name])){
          data[name] = [$(this).val()];
        }else{
          data[name].push($(this).val());
        }
      }else{
        data[name] = $(this).val();
      }
    });
    return data;
  };
  
  $.fn.writeWPError = function(wperror){
    return this.each(function(){
      var msgStr = '';
      $.each(wperror.errors, function(code, msg){
        msgStr = msg;
        return true;
      });
      $(this).html(msgStr);
    });
  };
  
  function getTemplate( id ){
    var settings = {
      evaluate: /<#([\s\S]+?)#>/g,
      interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
      escape: /\{\{([\s\S]+?)\}\}/g
    };
    return _.template(document.getElementById(id).innerHTML, settings);
  }
  function getJson( id ){
    return JSON.parse(document.getElementById(id).innerHTML);
  }
  
  //edit newsletter
  $(document).ready(function(){
    
    if(!$('#edit-newsletter-group').length)
      return;
      
    $('div.subscribers').each(function(){
      var $section = $(this);
      
      var statuses = getJson('statuses-json');
      var group = getJson('group-json');
      
      var templates = {
        edit: getTemplate('tmpl-edit-subscriber'),
        add: getTemplate('tmpl-add-subscriber'),
        row: getTemplate('tmpl-subscriber-row'),
        addBtn: getTemplate('tmpl-add-subscriber-btn'),
        vars: {group:group, statuses:statuses}
      };
      
      $.extend(group, {
        subscribersIdIndex: {},
        updateIdIndex: function(){
          var _this = this;
          $.each(group.subscribers, function(k,v){
            _this.subscribersIdIndex[v.id] = k;
          });
        },
        getIdIndex: function(id){
          return this.subscribersIdIndex[id];
        },
        updateSubscriber: function(id, data){
          this.subscribers[this.getIdIndex(id)] = data;
        },
        addSubscriber: function(data){
          this.subscribers.unshift(data);
          this.updateIdIndex();
        }
      });
      group.updateIdIndex();
      
      function makeRow(data){
        var templateVars = $.extend({}, templates.vars, {subscriber:data});
        var $row = $(templates.row(templateVars));
        
        $.extend($row, {
          $editRow: $(templates.edit(templateVars)),
          showEdit: function(){
            if($.contains(document.documentElement, this[0])){
              this.exchangeWith(this.$editRow);
            }
            return this;
          },
          showPlain: function(){
            if($.contains(document.documentElement, this.$editRow[0])){
              this.$editRow.exchangeWith(this);
            }
            return this;
          },
          replaceRow: function($_row){
            this.showPlain().exchangeWith($_row);
          }
        });
        
        $row.find('a.edit').click(function(){
          $row.showEdit();
        });
        
        $row.$editRow.find('button.cancel').click(function(){
          $row.showPlain();
        });
        
        $row.$editRow.find('button.save').click(function(){
          var data = $row.$editRow.find('form').first().extractFormData();
          
          var $spinner = $row.$editRow.find('.inline-edit-save .spinner');
          $spinner.addClass('is-active');
          
          var $error = $row.$editRow.find('.inline-edit-save .error').hide();
          
          $.ajax({
            url:ajaxUrl,
            data: data,
            dataType: 'json',
            method: 'post',
            success: function(response, status, jqXHR){
              $spinner.removeClass('is-active');
              if(typeof response != 'object'){
                console.error(jqXHR);
                $error.text('Error!').show();
              }else if(!response.success){
                $error.writeWPError(response.data.error).show();
              }else{
                group.updateSubscriber(response.data.subscriber.id, response.data.subscriber);
                $row.replaceRow(makeRow(response.data.subscriber));
              }
            },
            error: function(jqXHR, status, errorThrown){
              //display error
              $spinner.removeClass('is-active');
              console.error(jqXHR);
            }
          }); //ajax
        });
        
        return $row;
      }
      
      var $table = $section.find('.subscribers-table');
      
      //initial setup
      
      //add subscriber rows
      $tbody = $table.children('tbody');
      $.each(group.subscribers, function(k,v){
        $tbody.prepend(makeRow(v));
      });
      
      var $addBtn = $(templates.addBtn(templates.vars));
      $addBtn.$addForm = $(templates.add(templates.vars));
      
      $addBtn.find('button.open-form').click(function(){
        $addBtn.exchangeWith($addBtn.$addForm);
      });
      
      $addBtn.$addForm.find('button.cancel').click(function(){
        $addBtn.$addForm.exchangeWith($addBtn);
      });
      
      $addBtn.$addForm.find('button.save').click(function(){
        var data = $addBtn.$addForm.find('form').first().extractFormData();
        
        var $spinner = $addBtn.$addForm.find('.inline-edit-save .spinner');
        $spinner.addClass('is-active');
        
        var $error = $addBtn.$addForm.find('.inline-edit-save .error').hide();
        
        $.ajax({
          url:ajaxUrl,
          data: data,
          dataType: 'json',
          method: 'post',
          success: function(response, status, jqXHR){
            $spinner.removeClass('is-active');
            if(typeof response != 'object'){
              console.error(jqXHR);
              $error.text('Error!').show();
            }else if(!response.success){
              $error.writeWPError(response.data.error).show();
            }else{
              group.updateSubscriber(response.data.subscriber.id, response.data.subscriber);
              $addBtn.$addForm.after(makeRow(response.data.subscriber));
              $addBtn.$addForm.exchangeWith($addBtn);
            }
          },
          error: function(jqXHR, status, errorThrown){
            //display error
            $spinner.removeClass('is-active');
            console.error(jqXHR);
          }
        }); //ajax
      });
      
      $tbody.prepend($addBtn);
    }); //subscribers section each
  }); //document.ready
})(jQuery, _, ajaxurl);
