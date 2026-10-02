/* SNACKLICIOSOS · IMPRESIÓN POS-5890U-L / 58 mm */

(function(){
    'use strict';

    function printTicket58mm(){
        if (typeof buildTicket !== 'function') {
            alert('No se pudo preparar el ticket.');
            return;
        }

        buildTicket();

        const ticket = document.querySelector('#ticketContent .ticket');
        if(!ticket){
            alert('No se encontró el contenido del ticket.');
            return;
        }

        const printWindow = window.open(
            '',
            'snackliciosos_ticket_58mm',
            'width=330,height=760,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes'
        );

        if(!printWindow){
            alert('El navegador bloqueó la ventana de impresión. Permite ventanas emergentes para este sitio.');
            return;
        }

        const css = `
            @page{size:58mm auto;margin:0}
            *{box-sizing:border-box}
            html,body{
                margin:0!important;padding:0!important;
                width:58mm!important;min-width:58mm!important;max-width:58mm!important;
                background:#fff!important;
            }
            body{
                font-family:"Courier New",Courier,monospace;
                color:#111;font-size:10px;line-height:1.25;
            }
            .thermal-sheet{width:58mm;max-width:58mm;padding:1.5mm;margin:0}
            .ticket{
                width:55mm;max-width:55mm;margin:0 auto;padding:0;
                font-size:10px;line-height:1.25;overflow-wrap:anywhere;
            }
            .ticketHeader{text-align:center;padding:0 0 2mm}
            .ticketLogo{
                width:30px;height:30px;margin:0 auto 4px;border-radius:50%;
                display:grid;place-items:center;background:#ffe7f1;font-size:17px;
            }
            .ticketLogoImg{
                display:block;width:28mm;max-width:28mm;max-height:18mm;
                height:auto;object-fit:contain;margin:0 auto 3px;
            }
            .ticketHeader h1{
                margin:3px 0 0;font-family:Arial,sans-serif;font-size:17px;
                line-height:1.05;color:#111;overflow-wrap:anywhere;
            }
            .ticketBusinessData{
                margin-top:1.5px;font-size:8px;line-height:1.2;overflow-wrap:anywhere;
            }
            .ticketTitle{margin-top:2px;font-size:8px;font-weight:900;letter-spacing:.7px}
            .ticketMeta{
                display:grid;grid-template-columns:auto minmax(0,1fr);
                gap:2px 5px;margin-top:5px;font-size:8px;
            }
            .ticketMeta b{text-align:right;overflow-wrap:anywhere}
            .ticketDivider{border-top:1px dashed #555;margin:6px 0}
            .ticketProduct{
                display:grid;grid-template-columns:minmax(0,1fr) auto;
                gap:5px;margin-top:4px;align-items:start;
            }
            .ticketProduct div{display:flex;flex-direction:column;min-width:0}
            .ticketProduct b{font-size:9px;line-height:1.2;overflow-wrap:anywhere}
            .ticketProduct span,.ticketTopping{font-size:8px;line-height:1.2}
            .ticketProduct strong{font-size:9px;white-space:nowrap;text-align:right}
            .ticketTopping{
                padding-left:7px;display:flex;justify-content:space-between;
                gap:5px;min-width:0;
            }
            .ticketTopping span{font-weight:900;white-space:nowrap}
            .ticketRow{
                display:flex;justify-content:space-between;gap:5px;
                margin:3px 0;font-size:9px;
            }
            .ticketRow span{min-width:0;overflow-wrap:anywhere}
            .ticketRow b{font-size:10px;white-space:nowrap}
            .changeRow{padding:4px 5px;background:#eaf8f0}
            .ticketThanks{
                text-align:center;margin-top:8px;padding-top:6px;
                border-top:1px dashed #777;color:#111;font-weight:800;font-size:9px;
            }
        `;

        printWindow.document.open();
        printWindow.document.write(
            '<!doctype html><html lang="es"><head><meta charset="utf-8">' +
            '<title>Ticket Snackliciosos 58 mm</title><style>' + css +
            '</style></head><body><main class="thermal-sheet">' +
            ticket.outerHTML + '</main></body></html>'
        );
        printWindow.document.close();
        printWindow.focus();

        setTimeout(function(){
            try{ printWindow.print(); }catch(e){}
        },350);

        printWindow.addEventListener('afterprint',function(){
            setTimeout(function(){
                try{ printWindow.close(); }catch(e){}
            },300);
        });
    }

    window.printTicket58mm = printTicket58mm;

    function bind(){
        const button = document.getElementById('printTicket58');
        if(button){
            button.addEventListener('click', printTicket58mm);
        }
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', bind, {once:true});
    }else{
        bind();
    }
})();