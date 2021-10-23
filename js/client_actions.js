/**
 * changes the displayed values of #balance-amount, #charged-amount, #payed-amount 
 */
function addPrices(balance=null, charged=null, payed=null){
    //set balance amount
    let balance_amount = document.getElementById("balance-amount");
    let balance_input = document.getElementById("balance-input");
    if( balance_amount!=null && balance!=null && balance_input!=null){
        balance_input.value = parseFloat(balance_input.value)+parseFloat(balance);
        balance_amount.textContent = balance_input.value;
    }
    //set charged amount
    let charged_amount = document.getElementById("charged-amount");
    let charged_input = document.getElementById("charged-input");
    if( charged_amount!=null && charged!=null && charged_input!=null){
        charged_input.value = parseFloat(charged_input.value)+parseFloat(charged);
        charged_amount.textContent = charged_input.value;
    }
    //set payed amount
    let payed_amount = document.getElementById("payed-amount");
    let payed_input = document.getElementById("payed-input");
    if( payed_amount!=null && payed!=null && payed_input!=null){
        payed_input.value = parseFloat(payed_input.value)+parseFloat(payed);
        payed_amount.textContent = payed_input.value;
    }
}
/**
 * append new transaction to table list. This is only done client side.
 * In order for changes to be stored in database, the update button must be clicked 
 */
function addTransaction(){
    var table = document.getElementById('transactions_list');   //get containing table
    if( table!=null ){
        //--get input values and clear inputs
        let amount_input = document.getElementById('trans_amount');
        var amount = 0;
        if(amount_input!=null){
            amount = (amount_input.value==''?0: parseFloat(amount_input.value));
            amount_input.value='';
            if( amount < 0)
                addPrices(amount,amount,null);
            else
                addPrices(amount,null,amount);

        }
        let description_inp = document.getElementById('trans_description');
        var description = '';
        if( description_inp!=null ){
            description = description_inp.value;
            description_inp.value = ''; 
        }
        var date = new Date();
        let date_inp = document.getElementById('trans_date');
        if( trans_date!=null ){
            date = date_inp.value;
            date_inp.value = '';
        }

        //--create new table entry and append
        let tr = document.createElement('tr');
            let td_desc = document.createElement('td');
            td_desc.appendChild(document.createTextNode(description));
        tr.appendChild(td_desc);
            let td_amount = document.createElement('td');
            td_amount.appendChild(document.createTextNode(amount+"€"));
        tr.appendChild(td_amount);
            let td_date = document.createElement('td');
            td_date.appendChild(document.createTextNode(date));
        tr.appendChild(td_date);
            let td_close = document.createElement('td');
            td_close.appendChild(document.createTextNode('X'));
            td_close.addEventListener('click',function(){deleteEntry(tr);})
        tr.appendChild(td_close);
        tr.setAttribute('data-transaction','{"description":"'+description+'","amount":"'+amount+'","date":"'+date+'"}');
        tr.setAttribute('data-amount',amount);
        if( amount >= 0)
            tr.classList.add('income');
        else
            tr.classList.add('outcome');
        table.firstElementChild.appendChild(tr);

        var transactions = document.getElementById('transactions');
        if( transactions!=null ){
            transactions.value = transactions.value.replaceAll(' ','');
            //avoid adding ',' if this is the first entry
            if( transactions.value.charAt(transactions.value.length-3) == '[') 
                transactions.value = transactions.value.slice(0,-2) + '{"description":"'+description+'","amount":"'+amount+'","date":"'+date+'"}]}';
            else{
                transactions.value = transactions.value.slice(0,-2) + ',{"description":"'+description+'","amount":"'+amount+'","date":"'+date+'"}]}';
            }
        }
    }
}


/**
 * {nodeElement} tr_element = tr containing description, amount, date 
 */
function deleteEntry(tr_element){
    if( tr_element!=null ){
        //alter total amounts
        let amount = parseFloat(tr_element.getAttribute('data-amount'));
        if(amount>0)
            addPrices(-amount, null, -amount);
        else if(amount<0)
            addPrices(-amount, -amount, null);

        //--remove from textarea
        let transaction = tr_element.getAttribute('data-transaction');
        let textarea = document.getElementById("transactions");
        if( textarea.value.search('\\['+transaction+'\\]')!=-1 ){
            textarea.value = textarea.value.replace(transaction,'');
        }else if( textarea.value.search('\\['+transaction+',')!=-1 )
            textarea.value = textarea.value.replace(transaction+',','');
        else if( textarea.value.search(','+transaction)!=-1 )
            textarea.value = textarea.value.replace(','+transaction,'');

        //--remove from table
        tr_element.parentNode.removeChild(tr_element);
        
    }

}
