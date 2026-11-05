document.addEventListener("DOMContentLoaded", function () {
    let totalRowCount = 0;

    document.querySelectorAll("tbody").forEach(tbody => {
        totalRowCount += tbody.querySelectorAll("tr").length;
    });

    let fontSize = 16; 
    let padding = 8; 

    if (totalRowCount >= 18) {
        fontSize = 13.5;
        padding = 5.5;
    } else if (totalRowCount === 17) {
        fontSize = 14;
        padding = 6;
    } else if (totalRowCount === 16) {
        fontSize = 14.5;
        padding = 6;
    } else if (totalRowCount === 15) {
        fontSize = 14.5;
        padding = 6.5;
    } else if (totalRowCount === 14) {
        fontSize = 15;
        padding = 7.5;
    } else if (totalRowCount === 13) {
        fontSize = 15.5;
        padding = 8;
    }

    document.querySelectorAll("tbody").forEach(tbody => {
        tbody.style.fontSize = fontSize + "px";
    });
    document.querySelectorAll("td").forEach(td => {
        td.style.padding = padding + "px";
    });
});
