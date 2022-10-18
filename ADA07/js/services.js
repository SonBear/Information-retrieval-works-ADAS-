function getFromPLOS(search) {
  return new Promise((resolve, reject) => {
    fetch('http://api.plos.org/search?q=title:' + search)
      .then((response) => response.json())
      .then((data) => resolve(data.response.docs))
      .catch((error) => reject(error));
  });
}

function getCardPLOS(doc) {
  return `<div class="p-2 flex-fill bd-highlight">
                <div class="thumb">
                  <img src="img/popular_item_1.jpg" alt="" />
                  
                  <div class="text-content">
                    <h4>${doc.title_display.substring(0, 13) + '.....'}</h4>
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
  console.log('Hola');
  console.log(query);
  getFromPLOS(query)
    .then((docs) => {
      cardsContainerElement = document.getElementById('cards-container');
      cardsDocs = '';
      docs.forEach((doc) => {
        cardsDocs += getCardPLOS(doc);
      });
      cardsContainerElement.innerHTML = cardsDocs;
    })
    .catch((error) => {
      alert(error);
    });
}
