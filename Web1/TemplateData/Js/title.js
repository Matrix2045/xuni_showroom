//加载加载页信息
function SetHtml()
		{
		    var hallid=GetQueryString("hall_id");
			var httpRequest = new XMLHttpRequest();
            httpRequest.open('POST', 'http://showroom.pro4.liuniukeji.net/api/Index/getHallInfo', true);
            httpRequest.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            if(hallid==""){
			     httpRequest.send('hall_id=12&type=1');
			}else{
			     httpRequest.send('hall_id='+hallid+'&type=1');
			}
            httpRequest.onreadystatechange = function () {
               if (httpRequest.readyState == 4 && httpRequest.status == 200) {
                 var json = httpRequest.responseText;
				 var obj = JSON.parse(json);
				 document.title=obj.data.name;
				 document.getElementById("pp").src=obj.data.image;
				 document.getElementById("t1").innerHTML="主办人："+obj.data.curator;
				 document.getElementById("t2").innerHTML="主办单位："+obj.data.organizer;
               }
            };
		}
//获取域名中的参数
		function GetQueryString(name){
		     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
		     var r = window.location.search.substr(1).match(reg);
		     if(r!=null)return  unescape(r[2]); return "";
		}
//获取base64
function getBase64(imgUrl) {
           window.URL = window.URL || window.webkitURL;
           var xhr = new XMLHttpRequest();
            xhr.open("get", imgUrl, true);
            // 至关重要
            xhr.responseType = "blob";
            xhr.onload = function () {
              if (this.status == 200) {
              //得到一个blob对象
               var blob = this.response;
               // 至关重要
               let oFileReader = new FileReader();
               oFileReader.onloadend = function (e) {
               let base64 = e.target.result;
			    
               dealImage(base64, 1800, useImg);          
             };
             oFileReader.readAsDataURL(blob);
              }
           }
            xhr.send();
        }
//压缩图片
function dealImage(base64, w, callback) {
                var newImage = new Image();
                var quality = 0.5;    //压缩系数0-1之间
                newImage.src = base64;
                newImage.setAttribute("crossOrigin", 'Anonymous');	//url为外域时需要
                var imgWidth, imgHeight;
                newImage.onload = function () {
                    imgWidth = this.width;
                    imgHeight = this.height;
                    var canvas = document.createElement("canvas");
                    var ctx = canvas.getContext("2d");
                    if (Math.max(imgWidth, imgHeight) > w) {
                        if (imgWidth > imgHeight) {
                            canvas.width = w;
                            canvas.height = w * imgHeight / imgWidth;
                        } else {
                            canvas.height = w;
                            canvas.width = w * imgWidth / imgHeight;
                        }
                    } else {
                        canvas.width = imgWidth;
                        canvas.height = imgHeight;
                        quality = 0.5;
                    }
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(this, 0, 0, canvas.width, canvas.height);
                    var base64 = canvas.toDataURL("image/jpeg", quality); //压缩语句
                    // 如想确保图片压缩到自己想要的尺寸,如要求在50-150kb之间，请加以下语句，quality初始值根据情况自定
                    // while (base64.length / 1024 > 500) {
                     //	quality -= 0.01;
                     	//base64 = canvas.toDataURL("image/jpeg", quality);
                    // }
                     //防止最后一次压缩低于最低尺寸，只要quality递减合理，无需考虑
                     //while (base64.length / 1024 < 400) {
                     	//quality += 0.001;
                     	//base64 = canvas.toDataURL("image/jpeg", quality);
                    // }
                    callback(base64);//必须通过回调函数返回，否则无法及时拿到该值
                }
}
//回调
function useImg(base64) {
                gameInstance.SendMessage("HttpHelper","PicSuccess",base64);
}
//错误提示
function showError(str)
{
   document.getElementById("errorBrowserBlock").style.display = "inherit";
   document.getElementById("errorBrowserText").innerHTML=str;
}
//判断IOS版本
function GetIOSVersion()
{
   var str= navigator.userAgent.toLowerCase(); 
   var ver=str.match(/cpu iphone os (.*?) like mac os/);
    if(ver){
        str=ver[1].replace(/_/g,"."));
        s=str.split(".");
        alert(str);
        if(s[0]<13&&s[1]<3)
        {
          return false;
        }
    }
   return true;
}