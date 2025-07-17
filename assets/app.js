import './bootstrap.js';
import './styles/app.css';
import 'flowbite';
import 'toastr/build/toastr.min.css';
import toastr from 'toastr';
window.toastr = toastr;
toastr.options = {
    "showDuration": 0,      
    "hideDuration": 0,        
    "timeOut": 5000,         
    "extendedTimeOut": 1000, 
    "positionClass": "toast-top-right", 
    "closeButton": true,   
    "progressBar": true,    
    "preventDuplicates": true
};



