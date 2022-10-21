function getFromPLOS(search) {
  return new Promise((resolve, reject) => {
    fetch('http://api.plos.org/search?q=title:' + search)
      .then((response) => response.json())
      .then((data) => {
        var max_score = data.response.maxScore;
        var items = data.response.docs.map((item) => {
          var temp = item;
          item.type = 'PLOSAPI';
          item.n_score = item.score / max_score;
          return temp;
        });
        return resolve(items);
      })
      .catch((error) => reject(error));
  });
}

function getFromEuropean(search) {
  return new Promise((resolve, reject) => {
    fetch(
      'https://api.europeana.eu/record/v2/search.json?reusability=open&media=true&wskey=actocksh' +
        '&query=' +
        search
    )
      .then((response) => response.json())
      .then((data) => {
        var max_score = 0;
        var items = data.items;

        if (items && items[0].score) {
          max_score = items[0].score;
        }

        items = data.items.map((item) => {
          var temp = item;
          item.type = 'EuropeanAPI';
          item.n_score = item.score / max_score;
          return temp;
        });

        return resolve(items);
      })
      .catch((error) => reject(error));
  });
}
function parseJSON(response) {
  return response.text().then(function (text) {
    return text ? JSON.parse(text) : {};
  });
}
function searchTerms(query) {
  return new Promise((resolve, reject) => {
    fetch('https://api.datamuse.com/words?ml=' + query)
      .then((res) => console.log(res))
      .then((data) => {
        console.log(data);
      })
      .catch((err) => reject(err));
  });
}

function getCardPLOS(doc) {
  return `<div class="p-2 flex-fill bd-highlight">
                <div class="thumb">
                  <img src="img/plos_api.png" alt="" />
                  
                  <div class="text-content">
                    <h4>${doc.title_display}</h4>
                    <a href=${doc.id}>go to website</a>
                    <p>score: ${doc.score}</p>
                    <p>Normalized score: ${doc.n_score}</p>
                  </div>

                  <div class="plus-button">
                    <a href="#"><i class="fa fa-plus"></i></a>
                  </div>
                </div>
              </div>`;
}

function getCardEuropean(doc) {
  return `<div class="p-2 flex-fill bd-highlight">
                <div class="thumb">
                  <img src="img/europeana_api.png" alt="" />
                  
                  <div class="text-content">
                    <h4>${doc.title[0]}</h4>
                    <a href=${doc.id}>go to website</a>
                    <p>score: ${doc.score}</p>
                    <p>Normalized score: ${doc.n_score}</p>
                  </div>

                  <div class="plus-button">
                    <a href="#"><i class="fa fa-plus"></i></a>
                  </div>
                </div>
              </div>`;
}
function searchData() {
  var query = document.getElementById('query').value;

  var responsePromises = getResponseFromAPIs(query);

  Promise.all(responsePromises)
    .then((arrays) => {
      var items = [];
      arrays.forEach((arr) => {
        items = items.concat(arr);
      });
      console.log(items);
      return printData(items);
    })
    .catch((error) => {
      alert(error);
    });
}

function getResponseFromAPIs(query) {
  var isWithEuropean = document.getElementById('europeanaCB').checked;
  var isWithPLOS = document.getElementById('PLOSCB').checked;

  var promises = [];

  console.log(isWithEuropean);
  console.log(isWithPLOS);

  if (isWithEuropean) promises.push(getFromEuropean(query));
  if (isWithPLOS) promises.push(getFromPLOS(query));

  return promises;
}

function printData(items) {
  sortedItems = items.sort((a, b) =>
    a.n_score < b.n_score ? 1 : a.n_score > b.n_score ? -1 : 0
  );
  cardsDocs = '';
  sortedItems.forEach((item) => {
    if (item.type == 'EuropeanAPI') {
      cardsDocs += getCardEuropean(item);
    } else {
      cardsDocs += getCardPLOS(item);
    }
  });

  cardsContainerElement = document.getElementById('cards-container');
  cardsContainerElement.innerHTML = cardsDocs;
}

async function expandTerms(input) {
  var query = input.value;
  if (query != '') {
    var data = await searchTerms(query);
    console.log(data);
  }
}
