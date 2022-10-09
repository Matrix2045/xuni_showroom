define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/goods/index' + location.search,
                    add_url: 'goods/goods/add',
                    edit_url: 'goods/goods/edit',
                    del_url: 'goods/goods/del',
                    multi_url: 'goods/goods/multi',
                    table: 'goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'good_url', title: __('Good_url'), formatter: Table.api.formatter.url},
                        {field: 'good_name', title: __('Good_name')},
                        {field: 'short_title', title: __('Short_title')},
                        {field: 'shop_copywriting', title: __('Shop_copywriting')},
                        {field: 'circle_copywriting', title: __('Circle_copywriting')},
                        {field: 'video_url', title: __('Video_url'), formatter: Table.api.formatter.url},
                        {field: 'image1', title: __('Image1')},
                        {field: 'image2', title: __('Image2')},
                        {field: 'image3', title: __('Image3')},
                        {field: 'image4', title: __('Image4')},
                        {field: 'image5', title: __('Image5')},
                        {field: 'image6', title: __('Image6')},
                        {field: 'keywords', title: __('Keywords')},
                        {field: 'white_img', title: __('White_img')},
                        {field: 'marketing_img', title: __('Marketing_img')},
                        {field: 'cate_id', title: __('Cate_id')},
                        {field: 'good_type', title: __('Good_type')},
                        {field: 'activity_type', title: __('Activity_type')},
                        {field: 'coupon_url', title: __('Coupon_url'), formatter: Table.api.formatter.url},
                        {field: 'is_notice', title: __('Is_notice')},
                        {field: 'start_time', title: __('Start_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'end_time', title: __('End_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'original_price', title: __('Original_price'), operate:'BETWEEN'},
                        {field: 'coupon_price', title: __('Coupon_price'), operate:'BETWEEN'},
                        {field: 'post_coupon_price', title: __('Post_coupon_price'), operate:'BETWEEN'},
                        {field: 'commission_rate', title: __('Commission_rate'), operate:'BETWEEN'},
                        {field: 'shelf_status', title: __('Shelf_status')},
                        {field: 'approval_status', title: __('Approval_status')},
                        {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});