document.getElementById('burger').addEventListener('click', function(){
    if(document.querySelector('aside').style.left === "0px"){
        document.querySelector('aside').style.left = "-500px"
    }else{
        document.querySelector('aside').style.left = "0"
    }
})

document.getElementById('burgerDoc').addEventListener('click', function(){
    if(document.querySelector('aside').style.left === "0px"){
        document.querySelector('aside').style.left = "-500px"
    }else{
        document.querySelector('aside').style.left = "0"
    }
})