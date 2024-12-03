const months = [
    "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
    "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
];

let currentDate = new Date();
let currentMonth = currentDate.getMonth();
let currentYear = currentDate.getFullYear();
let today = currentDate.getDate();

function generateCalendar() {
    const calendarDiv = document.getElementById('calendar');
    calendarDiv.innerHTML = ''; // Limpar calendário anterior

    const monthTitle = document.createElement('div');
    monthTitle.className = 'month-title';
    monthTitle.innerHTML = `<img class='seta_calendario' src='imagens/seta.png' onclick='changeMonth(-1)' alt=''>` + months[currentMonth] + " " + currentYear + `<img class='seta_calendario2' src='imagens/seta.png' onclick='changeMonth(1)'alt=''>`;
    calendarDiv.appendChild(monthTitle);


    const table = document.createElement('table');
    table.classList.add('calendario');

    // Cabeçalho da tabela
    const headerRow = document.createElement('tr');
    const weekDays = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
    weekDays.forEach(day => {
        const th = document.createElement('th');
        th.classList.add('dias')
        th.textContent = day;
        headerRow.appendChild(th);
    });
    table.appendChild(headerRow);

    // Dias do mês
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    let row = document.createElement('tr');
    // Adicionar espaços vazios para o primeiro dia do mês
    for (let i = 0; i < firstDay; i++) {
        const td = document.createElement('td');
        row.appendChild(td);
    }
    // Adicionar dias do mês
    for (let day = 1; day <= daysInMonth; day++) {
        const td = document.createElement('td');

        td.textContent = day;
        td.id = "dia"+day+"-"+(currentMonth+1);
        td.onclick = function() {
            agendar(day, months[currentMonth], currentYear, currentMonth); // Passa a variável para a função
        };

        // Destacar o dia de hoje
        if (day === today && currentMonth === currentDate.getMonth() && currentYear === currentDate.getFullYear()) {
            td.className = 'today';
        }

        row.appendChild(td);

        // Se for sábado, adicionar uma nova linha
        if ((day + firstDay) % 7 === 0) {
            table.appendChild(row);
            row = document.createElement('tr');
        }
    }
    // Adicionar a última linha se não estiver vazia
    if (row.childNodes.length > 0) {
        table.appendChild(row);
    }

    calendarDiv.appendChild(table);
}

function changeMonth(direction) {
    currentMonth += direction;
    if (currentMonth < 0) {
        currentMonth = 11; // Dezembro
        currentYear--; // Decrementar o ano
    } else if (currentMonth > 11) {
        currentMonth = 0; // Janeiro
        currentYear++; // Incrementar o ano
    }
    generateCalendar(); // Gerar o calendário do novo mês
}
function agendar(dia, mes, ano, numMes) {

    enviarDados(dia, (numMes+1) , ano)
}

function agendarData(dia, mes, ano){
    divInfo = document.getElementById('confirm');

    divInfo.style.display = 'flex'

    const bElements = document.querySelectorAll('#confirm b');

    // Verifica se existem 3 elementos <b> e preenche com os dados
    if (bElements.length === 3) {
        bElements[0].textContent = dia;
        bElements[1].textContent = months[mes-1];
        bElements[2].textContent = ano;
    }

    if(dia < 10){
        dia = "0"+dia;
    }

    //const formattedDate = `${ano}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    
    // Define o valor do input de data
    document.getElementById('campoData').value = ano+"-"+mes+"-"+dia;

    console.log(ano+"-"+mes+"-"+dia)
}

function enviarDados(dia, mes, ano) {
    console.log(`Enviando dados: dia=${dia}, mes=${mes}, ano=${ano}`); // Log dos dados
    // Monta a URL com os parâmetros
    const url = `?dia=${dia}&mes=${mes}&ano=${ano}`;
    document.getElementById('campoData2').value = ano+"-"+mes+"-"+dia;
    // Redireciona para a mesma página com os parâmetros
    window.location.href = url;
}

function janelaAgendamento(dia, mes, ano){
    btnAgendar = document.getElementById('btnAgendar');
    btnAgendar.onclick = function() {
        agendarData(dia, mes, ano); // Passa a variável para a função
    };
}

// Gerar o calendário inicial automaticamente
generateCalendar();