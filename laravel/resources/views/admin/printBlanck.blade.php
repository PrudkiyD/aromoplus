<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Квитанція №{{$blanck->number}} | {{$blanck->aparat}}</title>
</head>
<body >
    
    


<style>

    table {
        font-family: Roboto, sans-serif;
        width: 900px;
        border-collapse: collapse;
    }

    th, td {
        border: 2px solid #131313;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
        padding: 0px;
    }

    .full-width {
        text-align: center;
    }

    .title-cell{
        width: 200px;
    }

    .footer{
        border: 2px solid #131313;
        padding: 8px;
        text-align: left;
        height: 100px;
    }
    .footer td{
        border: none;
        color:rgb(156, 156, 156);
        font-size: 13px;
    }
    
    .cell-3{
        width: 50px;
    }

    .close{
        background: none;
        border: none;
        text-decoration: underline;
        width: 100%;
    }

    .copyWindow{
        background-color: #13131374; position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: none; justify-content: center; align-items: center;
    }

    .windowCopy{
        padding: 10px 20px; background-color: white; border-radius: 5px; display: flex; flex-direction: column; gap: 10px;
    }

    @media only screen and (max-width: 600px) {
        .windowCopy{
            width: 100vw;
            height: 100vh;
        }

        .copyWindow{
            width: 100vw;
            height: 100vh;
        }
    }
    
</style>

<table title="Використайте комбінацію Ctrl+C для копіювання даних.">
    <thead>
        <tr>
            <th colspan="7" class="full-width" style="height: 70px;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 50px;">
                    <p class="number-rem" style="text-align: left; font-size: 30px;">Квитанція №{{$blanck->number}} <br>від {{ \Carbon\Carbon::parse($blanck->data)->translatedFormat('d F Y р.') }}</p>
                    <img height="100px" src="/media/service.png" alt="">
                </div>
                
                
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="title-cell" colspan="2">Клієнт:</td>
            <td class="name" colspan="5">{{$blanck->client}}</td>
        </tr>
        <tr>
            <td class="title-cell" colspan="2">Номер телефона:</td>
            <td class="phone" colspan="5">{{$blanck->phone}}</td>
        </tr>
        <tr>
            <td class="title-cell" colspan="2">Апарат:</td>
            <td class="aparat" colspan="5">{{$blanck->aparat}}</td>
        </tr>
        <tr>
            <td class="title-cell" colspan="2">Сервісний центр:</td>
            <td colspan="5">Полтава</td>
        </tr>
        <tr>
            <td class="title-cell" colspan="2">Заявленна несправність:</td>
            <td colspan="5">{{$blanck->defect}}</td>
        </tr>
        <tr>
            <td class="title-cell" colspan="2">Виявленні дефекти:</td>
            <td colspan="5">{{$blanck->fact_defect}}</td>
        </tr>
        <tr>
            <th colspan="7" class="full-width">Перелік відсутніх комплектуючих та зовнішніх пошкоджень</th>
        </tr>
        <tr>
            <td style="text-align: left; " colspan="7">{{$blanck->empty}}</td>
        <tr>
            <th colspan="7" class="full-width">Перелік послуг</th>
        </tr>
        <tr>
            <td class="cell-3" colspan="1">№ п/п</td>
            <td style="text-align: center;" colspan="5">Послуги</td>
            <td class="cell-3" style="text-align: center;" colspan="1">Ціна</td>
        </tr>
        <tr>
            <td class="cell-3" colspan="1">1</td>
            <td class="posluga" style="text-align: left;" colspan="5">
                {{ \App\Models\Remont\PayBlanck::TYPE_CHOICES[$pay->type] }}
            </td>
            <td class="cell-3 price" style="text-align: right;" colspan="1">{{$pay->total}}</td>
        </tr>
        <tr class="footer">
            <td colspan="3">
                <p>Апарат здав:</p>
                <p>підпис:________________________________</p>
                <p>П.І.Б.:________________________________</p>
            </td>
            <td colspan="1"></td>
            <td colspan="3" style="text-align: end;">
                <p>Апарат прийняв:</p>
                <p>підпис:________________________________</p>
                <p>П.І.Б.:________________________________</p>
            </td>
        </tr>

        <tr style="height: 20px; color: black; font-size: 15px;">
            <td colspan="7">
                Контакти майстерні: +380958405904
            </td>
        </tr>
    </tbody>
</table>

<div class="copyWindow" style="">
    <div class="windowCopy" style="">
        <div>
            <div style="margin-bottom: 10px;">Копіювати текст</div>
            <button class="excel" type="button">Реєстр</button>
            <button class="vider" type="button">Повідомлення</button>
        </div>
        
        <div><button class="close" type="button">Закрити</button></div>
    </div>
</div>


<script>

    var parts =  '{{ \Carbon\Carbon::parse($blanck->data)->translatedFormat('d F Y р.') }}'.split(' ');

    // Визначаємо день
    var day = parseInt(parts[0]);

    // Визначаємо місяць
    var month;
    switch (parts[1]) {
        case 'січня': month = 1; break;
        case 'лютого': month = 2; break;
        case 'березня': month = 3; break;
        case 'квітня': month = 4; break;
        case 'травня': month = 5; break;
        case 'червня': month = 6; break;
        case 'липня': month = 7; break;
        case 'серпня': month = 8; break;
        case 'вересня': month = 9; break;
        case 'жовтня': month = 10; break;
        case 'листопада': month = 11; break;
        case 'грудня': month = 12; break;
        default: month = 0; // За замовчуванням встановлюємо січень
    }

    // Визначаємо рік
    var year = parseInt(parts[2]);


    console.log(year, month, day);

    let payData = "{{ \App\Models\Remont\PayBlanck::PAY_CHOICES[$pay->pay] }}"
    let dateStart = "{{ \Carbon\Carbon::parse($blanck->data)->translatedFormat('d F Y р.') }}"
    let viberCopy = `Дата прийому: ${dateStart}\nКвитанція №{{$blanck->number}}\nПрізвище та ім'я: {{$blanck->client}} \nНомер телефону: {{$blanck->phone}}\nАпарат: {{$blanck->aparat}} \nОплата: ${payData}\nСума:  {{$pay->total}} грн. ` 
    let excelCopy = `{{$blanck->client}}\t{{$blanck->phone}}\t{{$blanck->number}}\t{{$pay->total}}\t${payData}\t${day}.${month}.${year}\t{{$blanck->aparat}}`

    function copyToClipboard(text) {
        var textarea = document.createElement("textarea");
        textarea.value = text;

        document.body.appendChild(textarea);

        textarea.select();

        document.execCommand("copy");

        document.body.removeChild(textarea);
    }

    document.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.key === 'c' || event.ctrlKey && event.key === 'с' ) {
            document.querySelector('.copyWindow').style.display = 'flex'
        }

        
    });

    window.addEventListener('click', function(el){
        if(el.target.className == 'close'){
            document.querySelector('.copyWindow').style.display = 'none'
        }

        if(el.target.className == 'vider'){
            copyToClipboard(viberCopy)
            document.querySelector('.copyWindow').style.display = 'none'
        }

        if(el.target.className == 'excel'){
            copyToClipboard(excelCopy)
            document.querySelector('.copyWindow').style.display = 'none'
        }
    })

    window.addEventListener('touchstart', function(){
        document.querySelector('.copyWindow').style.display = 'flex'
    })

</script>
</body>
</html>