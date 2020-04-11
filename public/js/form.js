function buildForm(method,action,data)
{
    // data = [
    //     {
    //         'name':'name',
    //         'value':'value',
    //     }
    // ];
    var form = $('<form></form>');
    // 设置属性
    form.attr('action', action);
    form.attr('method', method);
    // 创建Input
    for (var i = 0; i < data.length; i++) {
        formInput = $('<input type="hidden" name="'+data[i].name+'" />');
        formInput.attr('value', data[i].value);
        form.append(formInput);
    }
    $(document.body).append(form);
    // 提交表单
    form.submit();
    // console.log(form);
    // 注意return false取消链接的默认动作
    return false;
}
