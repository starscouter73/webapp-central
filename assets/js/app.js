async function loadNavigation() {
  const response = await fetch("./data/navigation.json");
  const data = await response.json();

  const navList = document.querySelector("[data-nav-list]");
  navList.innerHTML = "";

  data.sections.forEach((section) => {
    const item = document.createElement("li");
    item.textContent = section;
    navList.appendChild(item);
  });
}

loadNavigation().catch((error) => {
  console.error("Navigation konnte nicht geladen werden:", error);
});
