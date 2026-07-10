document.addEventListener('DOMContentLoaded', () => {
 const profitValues = [
    "$10,000",
    "$20,000",
    "$15,000",
    "$12,500",
    "$18,750",
    "$22,000",
    "$9,999"
  ];

  const now = new Date();
  const utcMidnight = Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate());
  const daysSinceEpoch = Math.floor(utcMidnight / (1000 * 60 * 60 * 24));

  const index = daysSinceEpoch % profitValues.length;
  const todayProfit = profitValues[index];

  document.querySelector(".profit-value").textContent = todayProfit;
 })
document.addEventListener('DOMContentLoaded', () => {

    const getExchangeLogo = (name) => ({ 'bybit': 'lyra-assets/bybit.png', 'bitmart': 'asset/lyra-assets/bitmart.png', 'bitget': 'lyra-assets/bitget.png', 'binance': 'lyra-assets/binance.png', 'kucoin': 'lyra-assets/kucoin.png', 'gateio': 'lyra-assets/gateio.png' }[name.toLowerCase()] || '');

    document.querySelectorAll('.accordion').forEach(accordion => {
        const button = accordion.querySelector('.accordion-header');
        const content = accordion.querySelector('.accordion-content');
        const openAccordion = () => {
            accordion.classList.add('active');
            content.style.maxHeight = content.scrollHeight + "px";
        };
        const closeAccordion = () => {
            accordion.classList.remove('active');
            content.style.maxHeight = '0px';
        };
        if (accordion.classList.contains('active')) setTimeout(openAccordion, 100);
        else closeAccordion();
        button.addEventListener('click', () => accordion.classList.contains('active') ? closeAccordion() : openAccordion());
    });

    document.querySelectorAll('.footer-tab-btn').forEach(tab => {
        tab.addEventListener('click', () => {
            const container = tab.closest('.history-container');
            container.querySelectorAll('.footer-tab-btn').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            container.querySelectorAll('.footer-content').forEach(c => c.classList.remove('active'));
            container.querySelector('#' + tab.dataset.tab).classList.add('active');
        });
    });

    const orderbookAsksBody = document.getElementById('orderbook-asks');
    const orderbookBidsBody = document.getElementById('orderbook-bids');
    const topExchangesBody = document.getElementById('top-exchanges-body');
    const liveTradesBody = document.getElementById('live-trades-body');

    let asksData = [ { exchange: 'bybit', size: 1.7010, price: 118988.2 }, { exchange: 'bitmart', size: 0.0073, price: 118983.08 }, { exchange: 'bitget', size: 0.5754, price: 118990.71 }, { exchange: 'binance', size: 4.9923, price: 118983.07 }, { exchange: 'kucoin', size: 0.4657, price: 118983.8 }, { exchange: 'gateio', size: 0.3243, price: 118995 } ];
    let bidsData = [ { exchange: 'bybit', size: 0.2487, price: 118988.1 }, { exchange: 'bitmart', size: 0.1645, price: 118983.07 }, { exchange: 'bitget', size: 2.0242, price: 118990.7 }, { exchange: 'binance', size: 0.3232, price: 118983.06 }, { exchange: 'kucoin', size: 1.2202, price: 118983.7 }, { exchange: 'gateio', size: 0.8270, price: 118994.9 } ];
    let topExchangesData = [ { exchange: 'binance', price: 118983.07 }, { exchange: 'bitmart', price: 118983.08 }, { exchange: 'kucoin', price: 118983.8 }, { exchange: 'bybit', price: 118988.2 }, { exchange: 'bitget', price: 118990.71 } ];
    
    const renderSidebars = () => {
        const maxAskSize = Math.max(...asksData.map(d => d.size));
        orderbookAsksBody.innerHTML = asksData.map(d => {
            const width = (d.size / maxAskSize) * 100;
            return `<tr style="background: linear-gradient(to right, var(--green-bg) ${width}%, transparent ${width}%)">
                <td><img src="${getExchangeLogo(d.exchange)}" class="exchange-logo">${d.exchange}</td>
                <td>${d.size.toFixed(4)}</td><td style="color:var(--red);text-align:right;">${d.price.toFixed(2)}</td></tr>`;
        }).join('');

        const maxBidSize = Math.max(...bidsData.map(d => d.size));
        orderbookBidsBody.innerHTML = bidsData.map(d => {
            const width = (d.size / maxBidSize) * 100;
            return `<tr style="background: linear-gradient(to right, var(--red-bg) ${width}%, transparent ${width}%)">
                <td><img src="${getExchangeLogo(d.exchange)}" class="exchange-logo">${d.exchange}</td>
                <td>${d.size.toFixed(4)}</td><td style="text-align:right;">${d.price.toFixed(2)}</td></tr>`;
        }).join('');
        
        topExchangesBody.innerHTML = topExchangesData.map((d, i) => `<tr><td>${i+1}</td><td><img src="${getExchangeLogo(d.exchange)}" class="exchange-logo">${d.exchange}</td><td style="text-align:right;">${d.price.toFixed(2)} USDT</td></tr>`).join('');
    };
    
    renderSidebars();

    setInterval(() => {
        // --- Update and Reorder Orderbook Data ---
        [asksData, bidsData].forEach(data => {
            data.forEach(item => {
                item.price += (Math.random() - 0.5) * 0.5;
                item.size += (Math.random() - 0.5) * 0.05;
                if (item.size < 0) item.size = 0.01;
            });
        });
        asksData.sort((a, b) => a.price - b.price);
        bidsData.sort((a, b) => b.price - a.price);

        // --- Update and Reorder Top Exchanges Data ---
        topExchangesData.forEach(item => {
             item.price += (Math.random() - 0.5) * 2;
        });
        topExchangesData.sort((a, b) => a.price - b.price);

        renderSidebars();
    }, 500);
});