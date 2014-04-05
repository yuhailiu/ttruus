$(function() {
    // Hover states on the static widgets
    $(".ui-state-default").hover(function() {
	$(this).addClass("ui-state-hover");
    }, function() {
	$(this).removeClass("ui-state-hover");
    });

    // get photo element
    var ownerPhoto = $("#owner_photo_img");
    // 在本地存储中保存图片
    var storageFiles = JSON.parse(sessionStorage.getItem("storageFiles")) || {};
    var info = JSON.parse(sessionStorage.getItem('ownerInfo'));
    // 检查数据，如果不存在，则创建一个本地存储
    if (typeof storageFiles.ownerPhoto === "undefined" || typeof info === "undefined") {
	// 设置图片
	ownerPhoto.attr("src", $("#owner_photo_img_url").val());
	// 图片加载完成后执行
	ownerPhoto.load(function() {
	    var ownerPhoto_element = document.getElementById("owner_photo_img");
	    var imgCanvas = document.createElement("canvas"), imgContext = imgCanvas.getContext("2d");
	    // 确保canvas尺寸和图片一致
	    imgCanvas.width = ownerPhoto_element.width;
	    imgCanvas.height = ownerPhoto_element.height;
	    // 在canvas中绘制图片
	    imgContext.drawImage(ownerPhoto_element, 0, 0, ownerPhoto_element.width, ownerPhoto_element.height);
	    // 将图片保存为Data URI
	    storageFiles.ownerPhoto = imgCanvas.toDataURL("image/png");
	    // 将JSON保存到本地存储中
	    try {
		sessionStorage.setItem("storageFiles", JSON.stringify(storageFiles));
	    } catch (e) {
		console.log("Storage failed: " + e);
	    }
	});

	// get user info and save the userinfo to sessionStroge
	$.getJSON('/users/setting/getOwnerInfo', function(data) {
	    sessionStorage.setItem('ownerInfo', JSON.stringify(data));
	    $("#showPhoto p").text(data.first_name);
	    updateUserInfo(data);
	});
    } else {
	// Use image from sessionStorage
	ownerPhoto.attr("src", storageFiles.ownerPhoto);

	$("#showPhoto p").text(info.first_name);
	updateUserInfo(info);
    }

    //hide the contents
    $(".content").hide();

    //show the default page-targets
    //$("#targets_div").show();
    $("#targets_a").click();

});

//init the owner's information
function updateUserInfo(data) {
    //data is a json userInfo
    $("#first_name_td").text(data.first_name);
    $("#first_name_input").attr('value', data.first_name);

    $("#last_name_td").text(data.last_name);
    $("#last_name_input").attr('value', data.last_name);

    $("#title_td").text(data.title);
    $("#title_input").attr('value', data.title);

    $("#create_time_td").text(data.create_time);
    $("#last_modify_td").text(data.last_modify);
    $("#self_descript_td").text(data.self_descript);
    $("#self_descript_input").attr('value', data.self_descript);

    $("#address_td").text(data.address);
    $("#address_input").attr('value', data.address);

    $("#email_td").text(data.email);
}
