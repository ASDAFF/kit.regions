window.KitRegions = function(arParams) {

    var wrap = document.getElementsByClassName("select-city__dropdown-wrap");
    var modal = document.getElementsByClassName("select-city__modal");
    var yes = document.getElementsByClassName("select-city__dropdown__choose__yes");
    var no = document.getElementsByClassName("select-city__dropdown__choose__no");
    var textCity = document.getElementsByClassName("select-city__block__text-city");
    var close = document.getElementsByClassName("select-city__close");


    var tabContent = document.getElementsByClassName("select-city__tab_content");


    var params = JSON.parse(arParams.arParams);

    try {
        if(yes.length) {
            for (let i = 0; i < yes.length; i++) {
                yes[i].addEventListener('click',function () {
                    wrap[0].style.display = 'none';
                    if(params.FROM_LOCATION == 'Y')
                    {
                        var idLocation = yes[0].dataset.id;
                        var idRegion = yes[0].dataset.regionId;
                        var codeRegion = yes[0].dataset.code;

                        SetCookie('kit_regions_location_id',idLocation,{'domain': '.' + arParams.rootDomain});
                        SetCookie('kit_regions_city_choosed','Y',{'domain': '.' + arParams.rootDomain});
                        SetCookie('kit_regions_id', idRegion,{'domain': '.' + arParams.rootDomain});
                        if(arParams.singleDomain != 'Y' && codeRegion)
                            document.location.href = codeRegion;
                    }
                    else{
                        SetCookie('kit_regions_city_choosed','Y',{'domain': '.' + arParams.rootDomain});
                        SetCookie('kit_regions_id',yes[0].dataset.id,{'domain': '.' + arParams.rootDomain});

                        if(arParams.singleDomain != 'Y')
                        {
                            var url = '';
                            for(var i = 0; i < arParams.list.length; ++i)
                            {
                                if(arParams.list[i]['ID'] == yes[0].dataset.id)
                                {
                                    url = arParams.list[i]['URL'];
                                }
                            }
                            if(url.length > 0)
                            {
                                document.location.href=url;
                            }
                        }
                        else
                        {
                            //location.reload();
                        }
                    }
                });
            }
        }

    } catch (e) {
        console.warn('Btn "Yes" not found ', e)
    }


    try {
        if(no.length) {
            for (let i = 0; i < no.length; i++) {
                no[i].addEventListener('click',function () {
                    Open();
                });
            }
        }
    } catch (e) {
        console.warn('Btn "No" not found ', e)
    }

    try {
        if (textCity.length) {
            for (let i = 0; i < textCity.length; i++) {
                textCity[i].addEventListener('click',function ()
                {
                    Open();
                });
            }
        }
    } catch (e) {
        console.warn('Btn "City" not found ', e)
    }

    function Open()
    {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", arParams.templateFolder+'/ajax.php', false);
        xhr.send();
        modal[0].innerHTML = xhr.responseText;
        close[0].addEventListener('click',function ()
        {
            Close();
        });

        var tab = document.getElementsByClassName("select-city__tab");
        for(var i = 0; i < tab.length; ++i){
            tab[i].addEventListener('click',function ()
            {
                if(this.classList.contains('active')){
                    return false;
                }
                for(var j = 0; j < tab.length; ++j)
                {
                    tab[j].classList.remove('active');
                }
                this.classList.add('active');
                for(var j = 0; j < tabContent.length; ++j){
                    tabContent[j].classList.remove('active');
                    if(tabContent[j].dataset.countryId == this.dataset.countryId){
                        tabContent[j].classList.add('active');
                    }
                }
            });
        }

        var input = document.getElementById("region-input");
        input.addEventListener('input',function(){
            var list = document.getElementsByClassName("select-city__list_item");
            var letters = document.getElementsByClassName("select-city__list_letter_wrapper");
            var value = this.value.toLowerCase();
            if(list.length){
                for(var i = 0; i < list.length; ++i){
                    list[i].style.display = "block";
                    let city = list[i].innerHTML.toLowerCase().trim();
                    if(value.length > 0){
                        if(city.substr(0,value.length) != value){
                            list[i].style.display = "none";
                        }
                    }
                }
            }
            if(letters.length){
                for(var i = 0; i < letters.length; ++i){
                    var was = false;
                    var child = letters[i].childNodes;
                    for(var j=0; j<child.length; ++j){
                        if(child[j].className == 'select-city__list'){
                            let child2 = child[j].childNodes;
                            if(child2.length){
                                for(var k=0; k<child2.length; ++k){
                                    if(child2[k].className == 'select-city__list_item'){
                                        let style = getComputedStyle(child2[k]);
                                        if(style.display != 'none'){
                                            was = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    letters[i].style.display = "block";
                    if(!was){
                        letters[i].style.display = "none";
                    }
                }
            }
        });

        let cityTitle = document.getElementsByClassName("select-city__modal__title");

        if(cityTitle.length)
        {
            if (cityTitle[0].children.length)
            {
                for (var i = 0; i < cityTitle[0].children.length; ++i)
                {
                    cityTitle[0].children[i].addEventListener('click', function ()
                    {
                        var idLocation = this.dataset.index;
                        if(idLocation !== undefined && idLocation > 0){
                            if(params.FROM_LOCATION == 'Y'){
                                SetCookie('kit_regions_location_id',idLocation,{'domain': '.' + arParams.rootDomain});
                                SetCookie('kit_regions_city_choosed','Y',{'domain': '.' + arParams.rootDomain});

                                var xhr = new XMLHttpRequest();
                                var body = 'id=' + idLocation+'&action=getDomainByLocation';
                                xhr.open("POST", arParams.componentFolder+'/ajax.php', false);
                                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                                xhr.onreadystatechange = function() {
                                    if (this.readyState != 4) return;
                                    var answer = JSON.parse(this.responseText);
                                    if(answer.ID){
                                        SetCookie('kit_regions_id',answer.ID,{'domain': '.' + arParams.rootDomain});
                                        if(arParams.singleDomain == 'Y')
                                        {
                                            location.reload();
                                        }
                                        else
                                        {
                                            document.location.href=answer.CODE;
                                        }
                                    }
                                }
                                xhr.send(body);
                            }
                        }
                    });
                }
            }
        }

        let cityInMessage = document.getElementsByClassName("select-city__under_input");
        if(cityInMessage.length){
            if(cityInMessage[0].children.length){
                for(var i =0;i<cityInMessage[0].children.length;++i){
                    cityInMessage[0].children[i].addEventListener('click',function (){
                        var idLocation = this.dataset.locationId;
                        if(idLocation !== undefined && idLocation > 0){
                            if(params.FROM_LOCATION == 'Y'){

                                SetCookie('kit_regions_location_id',idLocation,{'domain': '.' + arParams.rootDomain});
                                SetCookie('kit_regions_city_choosed','Y',{'domain': '.' + arParams.rootDomain});

                                var xhr = new XMLHttpRequest();
                                var body = 'id=' + idLocation+'&action=getDomainByLocation';
                                xhr.open("POST", arParams.componentFolder+'/ajax.php', false);
                                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                                xhr.onreadystatechange = function() {
                                    if (this.readyState != 4) return;
                                    var answer = JSON.parse(this.responseText);
                                    if(answer.ID){
                                        SetCookie('kit_regions_id',answer.ID,{'domain': '.' + arParams.rootDomain});
                                        if(arParams.singleDomain == 'Y')
                                        {
                                            location.reload();
                                        }
                                        else
                                        {
                                            document.location.href=answer.CODE;
                                        }
                                    }
                                }
                                xhr.send(body);
                            }
                        }
                    });
                }
            }
        }

        var item = document.getElementsByClassName("select-city__list_item");
        for(var i = 0; i < item.length; ++i)
        {
            item[i].addEventListener('click',function ()
            {
                if(params.FROM_LOCATION == 'Y')
                {
                    var id = this.getAttribute('data-index');
                    SetCookie('kit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});
                    SetCookie('kit_regions_location_id', id, {'domain': '.' + arParams.rootDomain});

                    var xhr = new XMLHttpRequest();
                    var body = 'id=' + encodeURIComponent(id) + '&action=getDomainByLocation';
                    xhr.open("POST", arParams.componentFolder + '/ajax.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    xhr.onreadystatechange = function ()
                    {
                        if (this.readyState != 4) return;
                        let answer = JSON.parse(this.responseText);
                        if (answer.ID)
                        {
                            SetCookie('kit_regions_id', answer.ID, {'domain': '.' + arParams.rootDomain});
                            if (arParams.singleDomain == 'Y')
                            {
                                location.reload();
                            }
                            else
                            {
                                document.location.href = answer.CODE;
                            }
                        }
                    }
                    xhr.send(body);
                }
                else{
                    var id = this.getAttribute('data-index');
                    SetCookie('kit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});
                    SetCookie('kit_regions_id', id, {'domain': '.' + arParams.rootDomain});
                    if (arParams.singleDomain == 'Y')
                    {
                        location.reload();
                    }
                    else
                    {
                        var url = '';
                        for(var i = 0; i < arParams.list.length; ++i)
                        {
                            if(arParams.list[i]['ID'] == id)
                            {
                                url = arParams.list[i]['URL'];
                            }
                        }
                        if(url.length > 0)
                        {
                            document.location.href=url;
                        }
                    }
                }
            });
        }

        wrap[0].style.display = 'none';
        if(!document.querySelector('body > .select-city__modal')) {
            document.body.appendChild(modal[0]);
        }
        document.querySelector("body > .select-city__modal").style.display = 'block';
    }
    function Close()
    {
        document.querySelector("body > .select-city__modal").style.display = 'none';
    }
    function SetCookie(name, value, options)
    {
        options = options || {};

        var expires = options.expires;

        if (typeof expires == "number" && expires)
        {
            var d = new Date();
            d.setTime(d.getTime() + expires * 1000);
            expires = options.expires = d;
        }
        if (expires && expires.toUTCString)
        {
            options.expires = expires.toUTCString();
        }
        options.path = '/';
        value = encodeURIComponent(value);

        var updatedCookie = name + "=" + value;

        for (var propName in options)
        {
            updatedCookie += "; " + propName;
            var propValue = options[propName];
            if (propValue !== true)
            {
                updatedCookie += "=" + propValue;
            }
        }
        document.cookie = updatedCookie;
    }
};

