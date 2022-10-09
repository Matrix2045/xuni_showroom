define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var Controller = {
        login: function () {//登录
            //为表单绑定事件
            Form.api.bindevent($("#login-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        register: function () {//注册
            $('#register-form .btn_register').click(function () {
                // 获取参数值
                var mobile = $('#mobile').val();
                var captcha = $('#captcha').val();
                var password = $('#password').val();
                var repassword = $('#repassword').val();
                // 验证
                if (mobile === "") {
                    Layer.msg('手机号不能为空');
                    return false;
                } else if (!mobile.match(/^1[3-9]\d{9}$/)) {
                    Layer.msg("请输入正确的手机号");
                    return false;
                }
            });

            //为表单绑定事件
            Form.api.bindevent($("#register-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        forgetpwd: function () {//忘记密码
            $('#resetpwd-form .btn_confirm').click(function () {
                // 获取参数值
                var mobile = $('#mobile').val();
                // 验证
                if (mobile === "") {
                    Layer.msg('手机号不能为空');return false;
                } else if (!mobile.match(/^1[3-9]\d{9}$/)) {
                    Layer.msg("请输入正确的手机号");return false;
                }
            });

            //为表单绑定事件
            Form.api.bindevent($("#resetpwd-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        index: function () {
            
        },
        setting: function () {
            //退出
            $(".logout").click(function(){
                $('.popup').show();
            })
            $(".cancel").click(function(){
                $('.popup').hide();
            })
            $(".sure").click(function(){
                location.href = "/index/User/logout";
            })
            
            // 给上传按钮添加上传成功事件
            $("#plupload-avatar").data("upload-success", function (data) {
                var url = Fast.api.cdnurl(data.url);
                Fast.api.ajax('/index/User/changeAvatar?avatar='+url, function (data, ret) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                });
            });
            Form.api.bindevent($("#profile-form"));
            
            //修改昵称
            $("[data-toggle='editUsername']").on('click', function () {
                var nickname = $(this).data('id');
                Layer.prompt({title: '修改昵称', formType: 0,value:nickname}, function (value, index) {
                    Fast.api.ajax('/index/User/changeinfo/nickname/'+value, function (data, ret) {
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    });
                });
            });
        },
        changepwd: function () {//修改密码
            $('.btn_sure').click(function () {
                // 获取参数值
                var mobile = $('#mobile').val();
                // 验证
                if (mobile === "") {
                    Layer.msg('手机号不能为空');return false;
                } else if (!mobile.match(/^1[3-9]\d{9}$/)) {
                    Layer.msg("请输入正确的手机号");return false;
                }
            });

            //为表单绑定事件
            Form.api.bindevent($("#changepwd-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        bind_mobile: function () {//绑定手机号
            $('.btn_sure').click(function () {
                // 获取参数值
                var mobile = $('#mobile').val();
                // 验证
                if (mobile === "") {
                    Layer.msg('手机号不能为空');return false;
                } else if (!mobile.match(/^1[3-9]\d{9}$/)) {
                    Layer.msg("请输入正确的手机号");return false;
                }
            });

            //为表单绑定事件
            Form.api.bindevent($("#bindmobile-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        apply_hall: function () {//申请开展馆
            $('.btn_sure').click(function () {
                // 获取参数值
                var name = $('#name').val();
                var mobile = $('#mobile').val();
                var address = $('#address').val();

                // 验证
                if (name === "") {
                    Layer.msg('请输入姓名');return false;
                }
                if (mobile === "") {
                    Layer.msg('请输入联系方式');return false;
                }
                if (address === "") {
                    Layer.msg('常在地');return false;
                }
            });

            //为表单绑定事件
            Form.api.bindevent($("#applyhall-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        works_info: function () {
            //点击大图展示
            $('.detail_img').click(function () {
                Layer.photos({
                    photos: '.detail_con',
                    anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
                });
            });
        },
    };
    return Controller;
});