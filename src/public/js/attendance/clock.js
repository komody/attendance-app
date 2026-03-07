/**
 * リアルタイムで日付と時刻を表示
 */
(function () {
  var weekdays = ['日', '月', '火', '水', '木', '金', '土'];
  function updateDisplay() {
    var now = new Date();
    var year = now.getFullYear();
    var month = now.getMonth() + 1;
    var date = now.getDate();
    var weekday = weekdays[now.getDay()];
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var dateElement = document.getElementById('current-date');
    var timeElement = document.getElementById('current-time');
    if (dateElement) {
      dateElement.textContent = year + '年' + month + '月' + date + '日(' + weekday + ')';
    }
    if (timeElement) {
      timeElement.textContent = hours + ':' + minutes;
    }
  }
  updateDisplay();
  setInterval(updateDisplay, 1000);
})();
