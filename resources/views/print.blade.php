<div id="preview"></div>

<script>
  const images = JSON.parse(localStorage.getItem("selectedImages") || "[]");
  const preview = document.getElementById("preview");
  images.forEach(src => {
    const img = document.createElement("img");
    img.src = src;
    img.style.width = "200px";
    img.style.margin = "10px";
    preview.appendChild(img);
  });
</script>
