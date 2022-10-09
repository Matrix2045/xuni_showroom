define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'works_browse/index' + location.search,
                    del_url: 'works_browse/del',
                    table: 'works_browse',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'w.id',
                search: false,
                exportTypes:['excel'],
                exportOptions: {
                    fileName: '浏览信息_' + Moment().format("YYYY-MM-DD"),
                    ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'u.nickname', title: __('User_id'),operate:'LIKE'},
                        {field: 'username', title: __('浏览用户姓名'),operate:'LIKE'},
                        {field: 'mobile', title: __('浏览用户手机号')},
                        {field: 'h.name', title: '所属展厅',operate:'LIKE'},
                        {field: 'works_id', title: '作品ID',operate:'LIKE'},
                        {field: 'w.name', title: __('Works_id'),operate:'LIKE'},
                        {field: 'browse_time', title: __('Browse_time'),
                            formatter:function(value){
                                return value+' 秒';
                            }
                            ,operate:false},
                        {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [{
                                    name: 'detail',
                                    text: '查看详情',
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'works_browse/detail'
                                }],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        detail: function () {
            $('body').on('click', '[data-tips-image]', function () {
                var img = new Image();
                var imgWidth = this.getAttribute('data-width') || '480px';
                img.onload = function () {
                    var $content = $(img).appendTo('body').css({background: '#fff', width: imgWidth, height: 'auto'});
                    Layer.open({
                        type: 1, area: imgWidth, title: false, closeBtn: 1,
                        skin: 'layui-layer-nobg', shadeClose: true, content: $content,
                        end: function () {
                            $(img).remove();
                        },
                        success: function () {

                        }
                    });
                };
                img.onerror = function (e) {

                };
                img.src = this.getAttribute('data-tips-image') || this.src;
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});